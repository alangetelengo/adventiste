<?php

namespace App\Http\Controllers;

use App\Models\EgliseLocale;
use App\Models\GroupeMission;
use App\Models\MembreHistoriqueStatut;
use App\Models\Membre;
use App\Models\TypeRecetteMission;
use App\Models\TypeStatutMembre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MembreController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Membre::class);

        $user = $request->user();
        $missionId = (int) $user->mission_id;

        $query = Membre::query()
            ->with(['egliseLocale', 'groupeMission', 'typeStatut'])
            ->orderBy('nom')
            ->orderBy('prenom');

        if ($user->eglise_locale_id !== null) {
            $query->where('eglise_locale_id', $user->eglise_locale_id);
        } else {
            $query->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId));
        }

        if ($request->filled('eglise_locale_id') && $user->eglise_locale_id === null) {
            $egliseId = (int) $request->input('eglise_locale_id');
            $ok = EgliseLocale::query()
                ->where('mission_id', $missionId)
                ->whereKey($egliseId)
                ->exists();
            if ($ok) {
                $query->where('eglise_locale_id', $egliseId);
            }
        }

        if ($request->filled('q')) {
            $q = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->string('q')->trim()).'%';
            $query->where(function ($sub) use ($q) {
                $sub->where('nom', 'like', $q)
                    ->orWhere('prenom', 'like', $q)
                    ->orWhere('telephone', 'like', $q);
            });
        }

        if ($request->filled('mode_entree')) {
            $query->where('mode_entree', $request->string('mode_entree'));
        }

        if ($request->filled('type_statut_membre_id')) {
            $typeId = (int) $request->input('type_statut_membre_id');
            $statutOk = TypeStatutMembre::query()
                ->where('mission_id', $missionId)
                ->whereKey($typeId)
                ->exists();
            if ($statutOk) {
                $query->where('type_statut_membre_id', $typeId);
            }
        }

        $membres = $query->paginate(20)->withQueryString();

        $eglisesFiltre = null;
        if ($user->eglise_locale_id === null) {
            $eglisesFiltre = EgliseLocale::query()
                ->where('mission_id', $missionId)
                ->orderBy('nom')
                ->get();
        }

        $modesEntree = Membre::labelsModesEntree();
        $typesStatut = TypeStatutMembre::query()
            ->actifsPourMission($missionId)
            ->get();

        $typesRecetteContribution = collect();
        if (
            $user->eglise_locale_id !== null
            && $user->can('create', \App\Models\RecapSabbatEglise::class)
        ) {
            $typesRecetteContribution = TypeRecetteMission::query()
                ->actifsPourMission($missionId)
                ->get();
        }

        return view('membres.index', compact('membres', 'eglisesFiltre', 'modesEntree', 'typesStatut', 'typesRecetteContribution'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Membre::class);

        [$eglises, $groupes, $egliseParDéfaut, $typesStatut] = $this->optionsFormulaire($request);

        return view('membres.create', compact('eglises', 'groupes', 'egliseParDéfaut', 'typesStatut'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Membre::class);

        $user = $request->user();
        $missionId = (int) $user->mission_id;

        $egliseRule = Rule::exists('eglises_locales', 'id')->where(fn ($q) => $q->where('mission_id', $missionId));

        $validated = $request->validate([
            'eglise_locale_id' => ['required', 'integer', $egliseRule],
            'groupe_mission_id' => [
                'nullable',
                'integer',
                Rule::exists('groupes_mission', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
            'mode_entree' => ['required', Rule::in(array_keys(Membre::labelsModesEntree()))],
            'type_bapteme_entree' => [
                'nullable',
                Rule::in(array_keys(Membre::labelsTypesBaptemeEntree())),
                Rule::requiredIf(fn () => $request->input('mode_entree') === Membre::MODE_ENTREE_BAPTEME),
            ],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'sexe' => ['nullable', 'string', 'max:32'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:64'],
            'adresses' => ['nullable', 'string', 'max:2000'],
            'niveau_etudes' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'situation_matrimoniale' => ['nullable', 'string', 'max:64'],
            'date_mariage' => ['nullable', 'date'],
            'conjoint' => ['nullable', 'string', 'max:255'],
            'date_bapteme' => ['nullable', 'date'],
            'lieu_bapteme' => ['nullable', 'string', 'max:255'],
            'religion_anterieure' => ['nullable', 'string', 'max:255'],
            'recu_dans_eglise_de' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $request->input('mode_entree') === Membre::MODE_ENTREE_TRANSFERT),
            ],
            'recu_le' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $request->input('mode_entree') === Membre::MODE_ENTREE_TRANSFERT),
            ],
            'baptise_par' => ['nullable', 'string', 'max:255'],
            'noms_pere' => ['nullable', 'string', 'max:255'],
            'noms_mere' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string', 'max:5000'],
            'type_statut_membre_id' => [
                'nullable',
                'integer',
                Rule::exists('types_statut_membres', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)->where('actif', true)),
            ],
        ]);

        if (! $this->peutChoisirGroupeMission($user)) {
            unset($validated['groupe_mission_id']);
        }

        if (($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_BAPTEME) {
            throw ValidationException::withMessages([
                'mode_entree' => 'Un membre par baptême doit être enregistré depuis le module Baptêmes.',
            ]);
        }

        if (($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_BAPTEME) {
            $validated['recu_dans_eglise_de'] = null;
            $validated['recu_le'] = null;
        }
        if (($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_TRANSFERT) {
            $validated['type_bapteme_entree'] = null;
            $validated['date_bapteme'] = null;
            $validated['lieu_bapteme'] = null;
            $validated['baptise_par'] = null;
        }
        if (($validated['mode_entree'] ?? null) !== Membre::MODE_ENTREE_TRANSFERT) {
            $validated['recu_dans_eglise_de'] = null;
            $validated['recu_le'] = null;
        }

        if ($user->eglise_locale_id !== null
            && (int) $validated['eglise_locale_id'] !== (int) $user->eglise_locale_id) {
            abort(403);
        }

        $statutId = $this->resolveTypeStatutId($request, $validated, $missionId);
        $validated['type_statut_membre_id'] = $statutId;
        $validated['actif'] = $this->isStatutActif($missionId, $statutId);

        $membre = Membre::query()->create(array_merge($validated, [
            'identifiant_public' => (string) Str::uuid(),
        ]));

        if ($statutId !== null) {
            MembreHistoriqueStatut::query()->create([
                'membre_id' => $membre->id,
                'type_statut_membre_id' => $statutId,
                'change_par_user_id' => $request->user()->id,
                'motif' => 'Initialisation du statut lors de la création du membre.',
                'changed_at' => now(),
            ]);
        }

        $message = ($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_TRANSFERT
            ? 'Membre transféré enregistré et activé.'
            : 'Membre enregistré.';

        return redirect()
            ->route('membres.edit', $membre)
            ->with('success', $message);
    }

    public function show(Request $request, Membre $membre): View
    {
        $this->authorize('view', $membre);

        $membre->load([
            'egliseLocale',
            'groupeMission',
            'typeStatut',
            'historiqueStatuts.typeStatut',
            'historiqueStatuts.changePar',
        ]);

        $typesStatut = collect();
        if ($request->user()->can('changeStatut', $membre)) {
            $missionId = (int) optional($membre->egliseLocale)->mission_id;
            if ($missionId > 0) {
                $typesStatut = TypeStatutMembre::query()
                    ->actifsPourMission($missionId)
                    ->get();
            }
        }

        return view('membres.show', compact('membre', 'typesStatut'));
    }

    public function edit(Request $request, Membre $membre): View
    {
        $this->authorize('update', $membre);

        [$eglises, $groupes, , $typesStatut] = $this->optionsFormulaire($request);

        return view('membres.edit', [
            'membre' => $membre,
            'eglises' => $eglises,
            'groupes' => $groupes,
            'egliseParDéfaut' => null,
            'typesStatut' => $typesStatut,
        ]);
    }

    public function update(Request $request, Membre $membre): RedirectResponse
    {
        $this->authorize('update', $membre);

        $user = $request->user();
        $missionId = (int) $user->mission_id;

        $egliseRule = Rule::exists('eglises_locales', 'id')->where(fn ($q) => $q->where('mission_id', $missionId));

        $validated = $request->validate([
            'eglise_locale_id' => ['required', 'integer', $egliseRule],
            'groupe_mission_id' => [
                'nullable',
                'integer',
                Rule::exists('groupes_mission', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
            'mode_entree' => ['required', Rule::in(array_keys(Membre::labelsModesEntree()))],
            'type_bapteme_entree' => [
                'nullable',
                Rule::in(array_keys(Membre::labelsTypesBaptemeEntree())),
                Rule::requiredIf(fn () => $request->input('mode_entree') === Membre::MODE_ENTREE_BAPTEME),
            ],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'sexe' => ['nullable', 'string', 'max:32'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:64'],
            'adresses' => ['nullable', 'string', 'max:2000'],
            'niveau_etudes' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'situation_matrimoniale' => ['nullable', 'string', 'max:64'],
            'date_mariage' => ['nullable', 'date'],
            'conjoint' => ['nullable', 'string', 'max:255'],
            'date_bapteme' => ['nullable', 'date'],
            'lieu_bapteme' => ['nullable', 'string', 'max:255'],
            'religion_anterieure' => ['nullable', 'string', 'max:255'],
            'recu_dans_eglise_de' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $request->input('mode_entree') === Membre::MODE_ENTREE_TRANSFERT),
            ],
            'recu_le' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $request->input('mode_entree') === Membre::MODE_ENTREE_TRANSFERT),
            ],
            'baptise_par' => ['nullable', 'string', 'max:255'],
            'noms_pere' => ['nullable', 'string', 'max:255'],
            'noms_mere' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string', 'max:5000'],
            'type_statut_membre_id' => [
                'nullable',
                'integer',
                Rule::exists('types_statut_membres', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)->where('actif', true)),
            ],
        ]);

        if (! $this->peutChoisirGroupeMission($user)) {
            unset($validated['groupe_mission_id']);
        }

        if (($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_BAPTEME
            && ! $membre->baptemes()->exists()) {
            throw ValidationException::withMessages([
                'mode_entree' => 'Pour un membre baptisé, utilisez le module Baptêmes afin de créer l\'acte d\'origine.',
            ]);
        }

        if (($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_BAPTEME) {
            $validated['recu_dans_eglise_de'] = null;
            $validated['recu_le'] = null;
        }
        if (($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_TRANSFERT) {
            $validated['type_bapteme_entree'] = null;
            $validated['date_bapteme'] = null;
            $validated['lieu_bapteme'] = null;
            $validated['baptise_par'] = null;
        }
        if (($validated['mode_entree'] ?? null) !== Membre::MODE_ENTREE_TRANSFERT) {
            $validated['recu_dans_eglise_de'] = null;
            $validated['recu_le'] = null;
        }

        if ($user->eglise_locale_id !== null
            && (int) $validated['eglise_locale_id'] !== (int) $user->eglise_locale_id) {
            abort(403);
        }

        $validated['type_statut_membre_id'] = $this->resolveTypeStatutId($request, $validated, $missionId);
        $validated['actif'] = $this->isStatutActif($missionId, $validated['type_statut_membre_id']);
        $ancienneValeur = (int) ($membre->type_statut_membre_id ?? 0);
        $membre->update($validated);

        $nouvelleValeur = (int) ($validated['type_statut_membre_id'] ?? 0);
        if ($ancienneValeur !== $nouvelleValeur && $nouvelleValeur > 0) {
            MembreHistoriqueStatut::query()->create([
                'membre_id' => $membre->id,
                'type_statut_membre_id' => $nouvelleValeur,
                'change_par_user_id' => $request->user()->id,
                'motif' => 'Statut ajusté depuis la fiche membre.',
                'changed_at' => now(),
            ]);
        }

        $message = ($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_TRANSFERT
            ? 'Fiche membre mise à jour (transfert activé).'
            : 'Fiche membre mise à jour.';

        return redirect()
            ->route('membres.edit', $membre)
            ->with('success', $message);
    }

    public function changeStatut(Request $request, Membre $membre): RedirectResponse
    {
        $this->authorize('changeStatut', $membre);

        $missionId = (int) optional($membre->egliseLocale)->mission_id;
        if ($missionId <= 0) {
            abort(422, 'Mission introuvable pour ce membre.');
        }

        $validated = $request->validate([
            'type_statut_membre_id' => [
                'required',
                'integer',
                Rule::exists('types_statut_membres', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)->where('actif', true)),
            ],
            'motif' => ['nullable', 'string', 'max:2000'],
        ]);

        $nouveauTypeId = (int) $validated['type_statut_membre_id'];
        if ((int) $membre->type_statut_membre_id === $nouveauTypeId) {
            return redirect()
                ->route('membres.show', $membre)
                ->with('success', 'Le membre est déjà dans ce statut.');
        }

        $membre->update([
            'type_statut_membre_id' => $nouveauTypeId,
            'actif' => $this->isStatutActif($missionId, $nouveauTypeId),
        ]);

        MembreHistoriqueStatut::query()->create([
            'membre_id' => $membre->id,
            'type_statut_membre_id' => $nouveauTypeId,
            'change_par_user_id' => $request->user()->id,
            'motif' => $validated['motif'] ?? null,
            'changed_at' => now(),
        ]);

        return redirect()
            ->route('membres.show', $membre)
            ->with('success', 'Statut du membre mis à jour.');
    }

    public function destroy(Membre $membre): RedirectResponse
    {
        $this->authorize('delete', $membre);

        $membre->delete();

        return redirect()
            ->route('membres.index')
            ->with('success', 'Membre supprimé.');
    }

    /**
     * @return array{
     *     0: \Illuminate\Database\Eloquent\Collection<int, EgliseLocale>,
     *     1: \Illuminate\Database\Eloquent\Collection<int, GroupeMission>,
     *     2: int|null,
     *     3: \Illuminate\Database\Eloquent\Collection<int, TypeStatutMembre>
     * }
     */
    private function optionsFormulaire(Request $request): array
    {
        $user = $request->user();
        $missionId = (int) $user->mission_id;

        $eglises = EgliseLocale::query()
            ->where('mission_id', $missionId)
            ->orderBy('nom')
            ->get();

        $groupes = GroupeMission::query()
            ->where('mission_id', $missionId)
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        $egliseParDéfaut = $user->eglise_locale_id;
        $typesStatut = TypeStatutMembre::query()
            ->actifsPourMission($missionId)
            ->get();

        return [$eglises, $groupes, $egliseParDéfaut, $typesStatut];
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function resolveTypeStatutId(Request $request, array $validated, int $missionId): ?int
    {
        if (isset($validated['type_statut_membre_id']) && $validated['type_statut_membre_id'] !== null) {
            return (int) $validated['type_statut_membre_id'];
        }

        if (($validated['mode_entree'] ?? null) === Membre::MODE_ENTREE_TRANSFERT) {
            $actif = TypeStatutMembre::query()
                ->where('mission_id', $missionId)
                ->where('code', 'actif')
                ->first();
            if ($actif) {
                return (int) $actif->id;
            }
        }

        $code = $request->boolean('actif', true) ? 'actif' : 'refroidi';
        $type = TypeStatutMembre::query()
            ->where('mission_id', $missionId)
            ->where('code', $code)
            ->first();

        return $type ? (int) $type->id : null;
    }

    private function peutChoisirGroupeMission(\App\Models\User $user): bool
    {
        return $user->hasRole('secretaire_executif_mission')
            || $user->hasRole('president_mission');
    }

    private function isStatutActif(int $missionId, ?int $statutId): bool
    {
        if ($statutId === null) {
            return true;
        }

        $code = TypeStatutMembre::query()
            ->where('mission_id', $missionId)
            ->whereKey($statutId)
            ->value('code');

        return $code !== 'refroidi';
    }
}
