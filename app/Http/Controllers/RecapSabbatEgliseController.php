<?php

namespace App\Http\Controllers;

use App\Models\DepartementMinistere;
use App\Models\EgliseLocale;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\Membre;
use App\Models\RecapSabbatEglise;
use App\Models\TypeRecetteMission;
use App\Services\Finances\RapportMensuelSyntheseService;
use App\Support\MontantFcfa;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RecapSabbatEgliseController extends Controller
{
    public function __construct(
        private readonly RapportMensuelSyntheseService $rapportMensuelSyntheseService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RecapSabbatEglise::class);

        $user = $request->user();
        $query = RecapSabbatEglise::query()
            ->with('egliseLocale')
            ->orderByDesc('date_sabbat');

        $egliseFiltre = null;

        if ($user->eglise_locale_id) {
            $query->where('eglise_locale_id', $user->eglise_locale_id);
            $egliseFiltre = $user->egliseLocale;
        } elseif ($user->mission_id) {
            $query->whereHas('egliseLocale', fn($q) => $q->where('mission_id', $user->mission_id));
        }

        $recaps = $query->paginate(20)->withQueryString();

        return view('finances.recaps.index', compact('recaps', 'egliseFiltre'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RecapSabbatEglise::class);

        $eglise = $request->user()->egliseLocale;
        abort_unless($eglise !== null, 403);

        $missionId = (int) $eglise->mission_id;
        $typesRecette = TypeRecetteMission::query()->actifsPourMission($missionId)->get();
        $departements = DepartementMinistere::query()
            ->where('eglise_locale_id', $eglise->id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'code_unique']);

        return view('finances.recaps.create', compact('typesRecette', 'departements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RecapSabbatEglise::class);

        $eglise = $request->user()->egliseLocale;
        abort_unless($eglise !== null, 403);
        $egliseId = (int) $eglise->id;
        $missionId = (int) $eglise->mission_id;

        $typeRule = Rule::exists('types_recette_mission', 'id')->where(
            fn($q) => $q->where('mission_id', $missionId)->where('actif', true)
        );

        $departementRule = Rule::exists('departements_ministeres', 'id')->where(
            fn($q) => $q->where('eglise_locale_id', $egliseId)->where('actif', true)
        );

        $this->prepareLignesAssembleeInput($request);

        $validated = $request->validate([
            'date_sabbat' => [
                'required',
                'date',
            ],
            'semaine_sabbat' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'lignes_assemblee' => ['nullable', 'array'],
            'lignes_assemblee.*.type_recette_id' => ['nullable', 'integer', $typeRule],
            'lignes_assemblee.*.departement_ministere_id' => ['nullable', 'integer', $departementRule],
            'lignes_assemblee.*.montant' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ($validated['lignes_assemblee'] ?? [] as $i => $ligne) {
            $m = (float) ($ligne['montant'] ?? 0);
            if ($m <= 0) {
                continue;
            }
            if (empty($ligne['type_recette_id'])) {
                throw ValidationException::withMessages([
                    "lignes_assemblee.{$i}.type_recette_id" => 'Choisissez un type de recette pour chaque montant.',
                ]);
            }
        }

        $date = Carbon::parse($validated['date_sabbat']);

        $recap = DB::transaction(function () use ($validated, $egliseId, $date, $missionId) {
            $recap = RecapSabbatEglise::query()->firstOrCreate(
                [
                    'eglise_locale_id' => $egliseId,
                    'date_sabbat' => $date->toDateString(),
                ],
                [
                    'identifiant_public' => (string) Str::uuid(),
                    'annee' => $date->year,
                    'mois' => $date->month,
                    'semaine_sabbat' => $validated['semaine_sabbat'] ?? (int) $date->weekOfMonth,
                    'statut' => 'brouillon',
                ]
            );

            if (! $recap->wasRecentlyCreated) {
                $recap->update([
                    'annee' => $date->year,
                    'mois' => $date->month,
                    'semaine_sabbat' => $validated['semaine_sabbat'] ?? $recap->semaine_sabbat ?? (int) $date->weekOfMonth,
                ]);
                $recap->lignesContributions()
                    ->whereIn('statut_ligne', [
                        LigneDimeOffrandeRecap::STATUT_BROUILLON,
                        LigneDimeOffrandeRecap::STATUT_REJETE,
                    ])
                    ->where('origine', LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE)
                    ->delete();
            }

            $ordre = 1;
            foreach ($validated['lignes_assemblee'] ?? [] as $ligne) {
                $montant = (float) ($ligne['montant'] ?? 0);
                if ($montant <= 0) {
                    continue;
                }
                $tid = (int) $ligne['type_recette_id'];
                $type = TypeRecetteMission::query()->where('mission_id', $missionId)->whereKey($tid)->firstOrFail();
                $parts = LigneDimeOffrandeRecap::repartirDepuisCategorie($type->categorie, $montant);
                $departementId = $ligne['departement_ministere_id'] ?? null;
                if ($departementId !== null && $departementId !== '') {
                    $departementId = (int) $departementId;
                } else {
                    $departementId = null;
                }
                LigneDimeOffrandeRecap::query()->create([
                    'recap_sabbat_eglise_id' => $recap->id,
                    'type_recette_id' => $tid,
                    'membre_id' => null,
                    'nom_visiteur' => null,
                    'departement_ministere_id' => $departementId,
                    'origine' => LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE,
                    'type_revenu' => $parts['type_revenu'],
                    'dimes' => $parts['dimes'],
                    'offrandes' => $parts['offrandes'],
                    'ordre_ligne' => $ordre++,
                    'statut_ligne' => LigneDimeOffrandeRecap::STATUT_BROUILLON,
                ]);
            }

            return $recap;
        });

        $recap->refresh()->synchroniserStatutDepuisLignes();
        $this->regenererRapportMensuel($recap);

        return redirect()
            ->route('finances.recaps.index')
            ->with('success', 'Sabbat enregistré avec succès. Vous pourrez le modifier plus tard si nécessaire.');
    }

    public function edit(Request $request, RecapSabbatEglise $recap): View
    {
        $this->authorize('view', $recap);

        $recap->load(['lignesContributions.typeRecette', 'egliseLocale']);

        $eglise = $recap->egliseLocale;
        $missionId = (int) $eglise->mission_id;
        $typesRecette = TypeRecetteMission::query()->actifsPourMission($missionId)->get();

        $lignesAssembleeForm = [];
        $lignesFigees = [];

        if (is_array(old('lignes_assemblee'))) {
            $lignesAssembleeForm = is_array(old('lignes_assemblee')) ? old('lignes_assemblee') : [];
            $lignesFigees = $recap->lignesContributions
                ->filter(fn(LigneDimeOffrandeRecap $l) => ! $l->estModifiableParTresorierEglise())
                ->values()
                ->all();
        } else {
            foreach ($recap->lignesContributions->sortBy('ordre_ligne') as $ligne) {
                if (! $ligne->estModifiableParTresorierEglise()) {
                    $lignesFigees[] = $ligne;

                    continue;
                }
                $row = $ligne->versLignesFormulaire()[0] ?? null;
                if ($row === null) {
                    continue;
                }
                if (($row['origine'] ?? '') === LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE) {
                    $lignesAssembleeForm[] = $row;
                }
            }
        }

        if ($lignesAssembleeForm === []) {
            $lignesAssembleeForm[] = [
                'id' => null,
                'type_recette_id' => '',
                'montant' => '',
            ];
        }
        $departements = DepartementMinistere::query()
            ->where('eglise_locale_id', $recap->eglise_locale_id)
            ->where('actif', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'code_unique']);

        return view('finances.recaps.edit', compact(
            'recap',
            'typesRecette',
            'lignesAssembleeForm',
            'lignesFigees',
            'departements'
        ));
    }

    public function update(Request $request, RecapSabbatEglise $recap): RedirectResponse
    {
        $this->authorize('update', $recap);

        $egliseRecapId = (int) $recap->eglise_locale_id;
        $eglise = EgliseLocale::query()->findOrFail($egliseRecapId);
        $missionId = (int) $eglise->mission_id;

        $typeRule = Rule::exists('types_recette_mission', 'id')->where(
            fn($q) => $q->where('mission_id', $missionId)->where('actif', true)
        );

        $departementRule = Rule::exists('departements_ministeres', 'id')->where(
            fn($q) => $q->where('eglise_locale_id', $egliseRecapId)->where('actif', true)
        );

        $this->prepareRecapFormArrays($request);

        $validated = $request->validate([
            'semaine_sabbat' => ['sometimes', 'nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'lignes_assemblee' => ['nullable', 'array'],
            'lignes_assemblee.*.type_recette_id' => ['nullable', 'integer', $typeRule],
            'lignes_assemblee.*.departement_ministere_id' => ['nullable', 'integer', $departementRule],
            'lignes_assemblee.*.montant' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ($validated['lignes_assemblee'] ?? [] as $i => $ligne) {
            $m = (float) ($ligne['montant'] ?? 0);
            if ($m <= 0) {
                continue;
            }
            if (empty($ligne['type_recette_id'])) {
                throw ValidationException::withMessages([
                    "lignes_assemblee.{$i}.type_recette_id" => 'Choisissez un type de recette pour chaque montant (assemblée).',
                ]);
            }
        }

        DB::transaction(function () use ($recap, $validated, $egliseRecapId, $missionId, $request) {
            if ($request->has('semaine_sabbat')) {
                $recap->update([
                    'semaine_sabbat' => $validated['semaine_sabbat'] ?? null,
                ]);
            }

            $recap->lignesContributions()
                ->whereIn('statut_ligne', [
                    LigneDimeOffrandeRecap::STATUT_BROUILLON,
                    LigneDimeOffrandeRecap::STATUT_REJETE,
                ])
                ->where('origine', LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE)
                ->delete();

            $ordre = 1;
            foreach ($validated['lignes_assemblee'] ?? [] as $ligne) {
                $montant = (float) ($ligne['montant'] ?? 0);
                if ($montant <= 0) {
                    continue;
                }
                $tid = (int) $ligne['type_recette_id'];
                $type = TypeRecetteMission::query()->where('mission_id', $missionId)->whereKey($tid)->firstOrFail();
                $parts = LigneDimeOffrandeRecap::repartirDepuisCategorie($type->categorie, $montant);
                $departementId = $ligne['departement_ministere_id'] ?? null;
                if ($departementId !== null && $departementId !== '') {
                    $departementId = (int) $departementId;
                } else {
                    $departementId = null;
                }
                LigneDimeOffrandeRecap::query()->create([
                    'recap_sabbat_eglise_id' => $recap->id,
                    'type_recette_id' => $tid,
                    'membre_id' => null,
                    'nom_visiteur' => null,
                    'departement_ministere_id' => $departementId,
                    'origine' => LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE,
                    'type_revenu' => $parts['type_revenu'],
                    'dimes' => $parts['dimes'],
                    'offrandes' => $parts['offrandes'],
                    'ordre_ligne' => $ordre++,
                    'statut_ligne' => LigneDimeOffrandeRecap::STATUT_BROUILLON,
                ]);
            }

        });

        $recap->refresh()->synchroniserStatutDepuisLignes();

        return redirect()
            ->route('finances.recaps.edit', $recap)
            ->with('success', 'Récap enregistré.');
    }

    public function soumettre(Request $request, RecapSabbatEglise $recap): RedirectResponse
    {
        $this->authorize('soumettre', $recap);

        if (! $recap->peutSoumettre()) {
            return redirect()
                ->route('finances.recaps.edit', $recap)
                ->with('error', 'Impossible de soumettre : corrigez les lignes refusées, ou attendez la validation des lignes déjà soumises.');
        }

        DB::table('lignes_dime_offrande_recap')
            ->where('recap_sabbat_eglise_id', $recap->id)
            ->where('statut_ligne', LigneDimeOffrandeRecap::STATUT_BROUILLON)
            ->update([
                'statut_ligne' => LigneDimeOffrandeRecap::STATUT_SOUMIS,
                'updated_at' => now(),
            ]);

        $recap->refresh()->synchroniserStatutDepuisLignes();
        $this->regenererRapportMensuel($recap);

        return redirect()
            ->route('finances.recaps.edit', $recap)
            ->with('success', 'Récap soumis à la mission. Les lignes sont en attente de validation.');
    }

    public function accepterMission(Request $request, RecapSabbatEglise $recap): RedirectResponse
    {
        $this->authorize('validerMission', $recap);

        if (! $recap->peutValiderParMission()) {
            return redirect()
                ->route('finances.recaps.edit', $recap)
                ->with('error', 'Ce récap ne peut pas être accepté (lignes non toutes « soumises »).');
        }

        DB::table('lignes_dime_offrande_recap')
            ->where('recap_sabbat_eglise_id', $recap->id)
            ->where('statut_ligne', LigneDimeOffrandeRecap::STATUT_SOUMIS)
            ->update([
                'statut_ligne' => LigneDimeOffrandeRecap::STATUT_VERROUILLE,
                'updated_at' => now(),
            ]);

        $recap->refresh()->synchroniserStatutDepuisLignes();
        $this->regenererRapportMensuel($recap);

        return redirect()
            ->route('finances.recaps.edit', $recap)
            ->with('success', 'Récap accepté : les lignes sont verrouillées.');
    }

    public function refuserMission(Request $request, RecapSabbatEglise $recap): RedirectResponse
    {
        $this->authorize('validerMission', $recap);

        if (! $recap->peutValiderParMission()) {
            return redirect()
                ->route('finances.recaps.edit', $recap)
                ->with('error', 'Ce récap ne peut pas être refusé dans son état actuel.');
        }

        DB::table('lignes_dime_offrande_recap')
            ->where('recap_sabbat_eglise_id', $recap->id)
            ->where('statut_ligne', LigneDimeOffrandeRecap::STATUT_SOUMIS)
            ->update([
                'statut_ligne' => LigneDimeOffrandeRecap::STATUT_REJETE,
                'updated_at' => now(),
            ]);

        $recap->refresh()->synchroniserStatutDepuisLignes();
        $this->regenererRapportMensuel($recap);

        return redirect()
            ->route('finances.recaps.edit', $recap)
            ->with('success', 'Récap refusé : l’église peut corriger les lignes concernées.');
    }

    public function createContributionMembre(Request $request, Membre $membre): View
    {
        $this->authorize('view', $membre);
        $this->authorize('create', RecapSabbatEglise::class);

        $user = $request->user();
        abort_unless($user->eglise_locale_id !== null, 403);
        abort_unless((int) $membre->eglise_locale_id === (int) $user->eglise_locale_id, 403);

        $eglise = $user->egliseLocale;
        $missionId = (int) $eglise->mission_id;
        $typesRecette = TypeRecetteMission::query()->actifsPourMission($missionId)->get();

        return view('finances.recaps.contribution-membre', compact('membre', 'typesRecette'));
    }

    public function storeContributionMembre(Request $request, Membre $membre): RedirectResponse
    {
        $this->authorize('view', $membre);
        $this->authorize('create', RecapSabbatEglise::class);

        $user = $request->user();
        abort_unless($user->eglise_locale_id !== null, 403);
        abort_unless((int) $membre->eglise_locale_id === (int) $user->eglise_locale_id, 403);

        $eglise = $user->egliseLocale;
        $egliseId = (int) $eglise->id;
        $missionId = (int) $eglise->mission_id;

        $typeRule = Rule::exists('types_recette_mission', 'id')->where(
            fn($q) => $q->where('mission_id', $missionId)->where('actif', true)
        );

        if ($request->has('montant')) {
            $request->merge([
                'montant' => MontantFcfa::parseToNumericString($request->input('montant')),
            ]);
        }
        if ($request->has('quantite_nature')) {
            $qn = $request->input('quantite_nature');
            $request->merge([
                'quantite_nature' => ($qn === '' || $qn === null) ? null : (float) str_replace(',', '.', (string) $qn),
            ]);
        }

        $validated = $request->validate([
            'date_sabbat' => [
                'required',
                'date',
            ],
            'type_recette_id' => ['required', 'integer', $typeRule],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'designation' => ['nullable', 'string', 'max:255'],
            'mode_don' => ['nullable', 'string', Rule::in([
                LigneDimeOffrandeRecap::MODE_DON_ARGENT,
                LigneDimeOffrandeRecap::MODE_DON_NATURE,
            ])],
            'destination_don' => ['nullable', 'string', Rule::in([
                LigneDimeOffrandeRecap::DESTINATION_DON_LOCALE,
                LigneDimeOffrandeRecap::DESTINATION_DON_MISSION,
            ])],
            'quantite_nature' => ['nullable', 'numeric', 'min:0'],
            'unite_nature' => ['nullable', 'string', 'max:64'],
        ]);

        $date = Carbon::parse($validated['date_sabbat']);

        $recap = DB::transaction(function () use ($egliseId, $date, $validated, $missionId, $membre) {
            $recap = RecapSabbatEglise::query()->firstOrCreate(
                [
                    'eglise_locale_id' => $egliseId,
                    'date_sabbat' => $date->toDateString(),
                ],
                [
                    'identifiant_public' => (string) Str::uuid(),
                    'annee' => $date->year,
                    'mois' => $date->month,
                    'statut' => 'brouillon',
                ]
            );

            if (! $recap->wasRecentlyCreated) {
                $recap->update([
                    'annee' => $date->year,
                    'mois' => $date->month,
                ]);
            }

            $tid = (int) $validated['type_recette_id'];
            $type = TypeRecetteMission::query()->where('mission_id', $missionId)->whereKey($tid)->firstOrFail();
            $montant = (float) $validated['montant'];
            $parts = LigneDimeOffrandeRecap::repartirDepuisCategorie($type->categorie, $montant);
            $modeDon = null;
            $destinationDon = null;
            $quantiteNature = null;
            $uniteNature = null;
            if ($type->categorie === TypeRecetteMission::CATEGORIE_DON) {
                $modeDon = (string) ($validated['mode_don'] ?? LigneDimeOffrandeRecap::MODE_DON_ARGENT);
                $destinationDon = (string) ($validated['destination_don'] ?? LigneDimeOffrandeRecap::DESTINATION_DON_LOCALE);
                $quantiteNature = isset($validated['quantite_nature']) ? (float) $validated['quantite_nature'] : null;
                $uniteNature = isset($validated['unite_nature']) && trim((string) $validated['unite_nature']) !== ''
                    ? trim((string) $validated['unite_nature'])
                    : null;
            }

            $maxOrdre = (int) LigneDimeOffrandeRecap::query()
                ->where('recap_sabbat_eglise_id', $recap->id)
                ->max('ordre_ligne');

            LigneDimeOffrandeRecap::query()->create([
                'recap_sabbat_eglise_id' => $recap->id,
                'type_recette_id' => $tid,
                'membre_id' => $membre->id,
                'nom_visiteur' => null,
                'designation' => isset($validated['designation']) && trim((string) $validated['designation']) !== ''
                    ? trim((string) $validated['designation'])
                    : null,
                'mode_don' => $modeDon,
                'destination_don' => $destinationDon,
                'quantite_nature' => $quantiteNature,
                'unite_nature' => $uniteNature,
                'origine' => LigneDimeOffrandeRecap::ORIGINE_INDIVIDUEL,
                'type_revenu' => $parts['type_revenu'],
                'dimes' => $parts['dimes'],
                'offrandes' => $parts['offrandes'],
                'ordre_ligne' => $maxOrdre + 1,
                'statut_ligne' => LigneDimeOffrandeRecap::STATUT_BROUILLON,
            ]);

            return $recap;
        });

        $recap->refresh()->synchroniserStatutDepuisLignes();

        return redirect()
            ->route('membres.index')
            ->with('success', 'Recette de l’église enregistrée. Elle sera prise en compte dans le rapport mensuel de la période.');
    }

    private function regenererRapportMensuel(RecapSabbatEglise $recap): void
    {
        $recap->loadMissing('egliseLocale');
        $eglise = $recap->egliseLocale;
        if ($eglise !== null) {
            $this->rapportMensuelSyntheseService->regenererPourEgliseEtMois(
                $eglise,
                (int) $recap->annee,
                (int) $recap->mois
            );
        }
    }

    private function prepareLignesAssembleeInput(Request $request): void
    {
        $rows = $request->input('lignes_assemblee');
        if (! is_array($rows)) {
            return;
        }
        foreach ($rows as $i => $ligne) {
            if (! is_array($ligne)) {
                continue;
            }
            $rows[$i]['montant'] = MontantFcfa::parseToNumericString($ligne['montant'] ?? null);
        }
        $request->merge(['lignes_assemblee' => $rows]);
    }

    private function prepareRecapFormArrays(Request $request): void
    {
        if ($request->has('semaine_sabbat')) {
            $ss = $request->input('semaine_sabbat');
            if ($ss === '' || $ss === null) {
                $request->merge(['semaine_sabbat' => null]);
            } else {
                $request->merge(['semaine_sabbat' => (int) $ss]);
            }
        }

        foreach (['lignes_assemblee'] as $key) {
            $rows = $request->input($key);
            if (! is_array($rows)) {
                continue;
            }
            foreach ($rows as $i => $ligne) {
                if (! is_array($ligne)) {
                    continue;
                }
                $rows[$i]['montant'] = MontantFcfa::parseToNumericString($ligne['montant'] ?? null);
            }
            $request->merge([$key => $rows]);
        }
    }
}
