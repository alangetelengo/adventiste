<?php

namespace App\Http\Controllers;

use App\Models\Bapteme;
use App\Models\EgliseLocale;
use App\Models\Membre;
use App\Models\MembreHistoriqueStatut;
use App\Models\NotificationInterne;
use App\Models\RapportMembreEglise;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RapportMembreEgliseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RapportMembreEglise::class);

        $user = $request->user();
        $query = RapportMembreEglise::query()
            ->with(['egliseLocale', 'soumisPar', 'revuPar'])
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->orderByDesc('id');

        $eglisesFiltre = null;
        $missionId = (int) $user->mission_id;
        if ($user->eglise_locale_id !== null) {
            $query->where('eglise_locale_id', (int) $user->eglise_locale_id);
        } elseif ($missionId > 0) {
            $query->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId));
            $eglisesFiltre = EgliseLocale::query()
                ->where('mission_id', $missionId)
                ->orderBy('nom')
                ->get();
        }

        if ($request->filled('type_periode')) {
            $query->where('type_periode', (string) $request->input('type_periode'));
        }
        if ($request->filled('etat')) {
            $query->where('etat', (string) $request->input('etat'));
        }
        if ($request->filled('annee')) {
            $query->where('annee', (int) $request->input('annee'));
        }
        if ($request->filled('eglise_locale_id') && $user->eglise_locale_id === null) {
            $egliseId = (int) $request->input('eglise_locale_id');
            $ok = EgliseLocale::query()->where('mission_id', $missionId)->whereKey($egliseId)->exists();
            if ($ok) {
                $query->where('eglise_locale_id', $egliseId);
            }
        }

        $rapports = $query->paginate(20)->withQueryString();
        $typesPeriode = RapportMembreEglise::labelsTypesPeriode();
        $etats = RapportMembreEglise::labelsEtats();

        return view('secretariat.rapports-membres.index', compact('rapports', 'typesPeriode', 'etats', 'eglisesFiltre'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RapportMembreEglise::class);

        return view('secretariat.rapports-membres.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RapportMembreEglise::class);

        $egliseId = (int) $request->user()->eglise_locale_id;
        abort_if($egliseId <= 0, 403);

        $validated = $request->validate([
            'type_periode' => ['required', Rule::in(array_keys(RapportMembreEglise::labelsTypesPeriode()))],
            'annee' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mois' => ['nullable', 'integer', 'min:1', 'max:12'],
            'notes_locales' => ['nullable', 'string', 'max:5000'],
        ]);

        $mois = $validated['type_periode'] === RapportMembreEglise::TYPE_ANNUEL
            ? 0
            : (int) ($validated['mois'] ?? now()->month);

        $existant = RapportMembreEglise::query()
            ->where('eglise_locale_id', $egliseId)
            ->where('type_periode', $validated['type_periode'])
            ->where('annee', (int) $validated['annee'])
            ->where('mois', $mois)
            ->first();

        if ($existant !== null) {
            return redirect()
                ->route('secretariat.rapports-membres.show', $existant)
                ->with('info', 'Un rapport existe déjà pour cette période.');
        }

        $stats = $this->calculerStats($egliseId, $validated['type_periode'], (int) $validated['annee'], $mois);
        $rapport = RapportMembreEglise::query()->create(array_merge($stats, [
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $egliseId,
            'type_periode' => $validated['type_periode'],
            'annee' => (int) $validated['annee'],
            'mois' => $mois,
            'etat' => RapportMembreEglise::ETAT_BROUILLON,
            'notes_locales' => $validated['notes_locales'] ?? null,
        ]));

        return redirect()
            ->route('secretariat.rapports-membres.show', $rapport)
            ->with('success', 'Rapport membres généré.');
    }

    public function show(RapportMembreEglise $rapport): View
    {
        $this->authorize('view', $rapport);
        $rapport->load(['egliseLocale', 'soumisPar', 'revuPar']);

        return view('secretariat.rapports-membres.show', [
            'rapport' => $rapport,
            'typesPeriode' => RapportMembreEglise::labelsTypesPeriode(),
            'etats' => RapportMembreEglise::labelsEtats(),
        ]);
    }

    public function impression(RapportMembreEglise $rapport): View
    {
        $this->authorize('view', $rapport);
        $rapport->load(['egliseLocale', 'soumisPar', 'revuPar']);

        return view('secretariat.rapports-membres.impression', [
            'rapport' => $rapport,
            'typesPeriode' => RapportMembreEglise::labelsTypesPeriode(),
            'etats' => RapportMembreEglise::labelsEtats(),
        ]);
    }

    public function edit(RapportMembreEglise $rapport): View
    {
        $this->authorize('update', $rapport);

        return view('secretariat.rapports-membres.edit', [
            'rapport' => $rapport,
            'typesPeriode' => RapportMembreEglise::labelsTypesPeriode(),
        ]);
    }

    public function update(Request $request, RapportMembreEglise $rapport): RedirectResponse
    {
        $this->authorize('update', $rapport);

        $validated = $request->validate([
            'notes_locales' => ['nullable', 'string', 'max:5000'],
            'recalculer' => ['nullable', Rule::in(['0', '1'])],
        ]);

        if (($validated['recalculer'] ?? '0') === '1') {
            $stats = $this->calculerStats(
                (int) $rapport->eglise_locale_id,
                (string) $rapport->type_periode,
                (int) $rapport->annee,
                (int) $rapport->mois
            );
            $rapport->fill($stats);
        }

        $rapport->notes_locales = $validated['notes_locales'] ?? null;
        $rapport->save();

        return redirect()
            ->route('secretariat.rapports-membres.show', $rapport)
            ->with('success', 'Rapport mis à jour.');
    }

    public function destroy(RapportMembreEglise $rapport): RedirectResponse
    {
        $this->authorize('delete', $rapport);
        $rapport->delete();

        return redirect()
            ->route('secretariat.rapports-membres.index')
            ->with('success', 'Rapport supprimé.');
    }

    public function soumettre(Request $request, RapportMembreEglise $rapport): RedirectResponse
    {
        $this->authorize('soumettre', $rapport);

        $rapport->update([
            'etat' => RapportMembreEglise::ETAT_SOUMIS,
            'soumis_le' => now(),
            'soumis_par_user_id' => $request->user()->id,
            'revu_le' => null,
            'revu_par_user_id' => null,
            'commentaire_mission' => null,
        ]);

        $this->notifierSoumissionMission($rapport, (int) $request->user()->id);

        return redirect()
            ->route('secretariat.rapports-membres.show', $rapport)
            ->with('success', 'Rapport soumis à la mission.');
    }

    public function valider(Request $request, RapportMembreEglise $rapport): RedirectResponse
    {
        $this->authorize('review', $rapport);

        $validated = $request->validate([
            'commentaire_mission' => ['nullable', 'string', 'max:3000'],
        ]);

        $rapport->update([
            'etat' => RapportMembreEglise::ETAT_VALIDE,
            'revu_le' => now(),
            'revu_par_user_id' => $request->user()->id,
            'commentaire_mission' => $validated['commentaire_mission'] ?? null,
        ]);

        $this->notifierRetourEglise($rapport, (int) $request->user()->id, true);

        return redirect()
            ->route('secretariat.rapports-membres.show', $rapport)
            ->with('success', 'Rapport validé par la mission.');
    }

    public function rejeter(Request $request, RapportMembreEglise $rapport): RedirectResponse
    {
        $this->authorize('review', $rapport);

        $validated = $request->validate([
            'commentaire_mission' => ['required', 'string', 'max:3000'],
        ]);

        $rapport->update([
            'etat' => RapportMembreEglise::ETAT_REJETE,
            'revu_le' => now(),
            'revu_par_user_id' => $request->user()->id,
            'commentaire_mission' => $validated['commentaire_mission'],
        ]);

        $this->notifierRetourEglise($rapport, (int) $request->user()->id, false);

        return redirect()
            ->route('secretariat.rapports-membres.show', $rapport)
            ->with('success', 'Rapport rejeté avec commentaire.');
    }

    /**
     * @return array<string, mixed>
     */
    private function calculerStats(int $egliseId, string $typePeriode, int $annee, int $mois): array
    {
        [$debut, $fin] = $this->bornesPeriode($typePeriode, $annee, $mois);

        $totalMembres = Membre::query()
            ->where('eglise_locale_id', $egliseId)
            ->count();

        $baptemes = Bapteme::query()
            ->where('eglise_locale_id', $egliseId)
            ->whereBetween('date_bapteme', [$debut->toDateString(), $fin->toDateString()]);

        $totalImmersion = (clone $baptemes)
            ->where('type_bapteme', Membre::TYPE_BAPTEME_IMMERSION)
            ->count();
        $totalProfessionFoi = (clone $baptemes)
            ->where('type_bapteme', Membre::TYPE_BAPTEME_PROFESSION_FOI)
            ->count();

        $totalTransferts = Membre::query()
            ->where('eglise_locale_id', $egliseId)
            ->where('mode_entree', Membre::MODE_ENTREE_TRANSFERT)
            ->whereBetween('recu_le', [$debut->toDateString(), $fin->toDateString()])
            ->count();

        $totalChangementsStatut = MembreHistoriqueStatut::query()
            ->whereHas('membre', fn ($q) => $q->where('eglise_locale_id', $egliseId))
            ->whereBetween('changed_at', [$debut->toDateTimeString(), $fin->endOfDay()->toDateTimeString()])
            ->count();

        $statsStatuts = Membre::query()
            ->where('eglise_locale_id', $egliseId)
            ->leftJoin('types_statut_membres', 'types_statut_membres.id', '=', 'membres.type_statut_membre_id')
            ->selectRaw('COALESCE(types_statut_membres.libelle, "Non classé") as libelle, COUNT(*) as total')
            ->groupBy('libelle')
            ->orderBy('libelle')
            ->get()
            ->map(fn ($row) => ['libelle' => (string) $row->libelle, 'total' => (int) $row->total])
            ->all();

        return [
            'total_membres' => $totalMembres,
            'total_baptemes_immersion' => $totalImmersion,
            'total_baptemes_profession_foi' => $totalProfessionFoi,
            'total_entrees_transfert' => $totalTransferts,
            'total_changements_statut' => $totalChangementsStatut,
            'stats_statuts' => $statsStatuts,
        ];
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function bornesPeriode(string $typePeriode, int $annee, int $mois): array
    {
        if ($typePeriode === RapportMembreEglise::TYPE_ANNUEL) {
            $debut = Carbon::create($annee, 1, 1)->startOfDay();
            $fin = Carbon::create($annee, 12, 31)->endOfDay();

            return [$debut, $fin];
        }

        $moisEffectif = max(1, min(12, $mois));
        $debut = Carbon::create($annee, $moisEffectif, 1)->startOfDay();
        $fin = (clone $debut)->endOfMonth()->endOfDay();

        return [$debut, $fin];
    }

    private function notifierSoumissionMission(RapportMembreEglise $rapport, int $emetteurUserId): void
    {
        $eglise = $rapport->egliseLocale()->with('mission')->first();
        if (! $eglise || ! $eglise->mission_id) {
            return;
        }

        $destinataires = User::query()
            ->where('mission_id', (int) $eglise->mission_id)
            ->whereNull('eglise_locale_id')
            ->where('id', '<>', $emetteurUserId)
            ->whereHas('role', fn ($q) => $q->where('name', 'secretaire_executif_mission'))
            ->get(['id']);

        foreach ($destinataires as $user) {
            NotificationInterne::query()->create([
                'user_id' => (int) $user->id,
                'type' => 'rapport_membre_soumis',
                'title' => 'Nouveau rapport membres soumis',
                'message' => 'L’église '.$rapport->egliseLocale->nom.' a soumis un rapport '.$rapport->type_periode.' '.$rapport->annee.'.',
                'url' => route('secretariat.rapports-membres.show', $rapport),
                'data' => ['rapport_id' => $rapport->id],
            ]);
        }
    }

    private function notifierRetourEglise(RapportMembreEglise $rapport, int $emetteurUserId, bool $estValidation): void
    {
        $destinataires = User::query()
            ->where('eglise_locale_id', (int) $rapport->eglise_locale_id)
            ->where('id', '<>', $emetteurUserId)
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['secretaire_eglise', 'secretaire']))
            ->get(['id']);

        if ($rapport->soumis_par_user_id && $rapport->soumis_par_user_id !== $emetteurUserId) {
            $destinataires->push((object) ['id' => (int) $rapport->soumis_par_user_id]);
        }

        $destinataires = $destinataires->unique('id');
        $title = $estValidation ? 'Rapport membres validé' : 'Rapport membres rejeté';
        $message = $estValidation
            ? 'La mission a validé votre rapport membres.'
            : 'La mission a rejeté votre rapport membres. Consultez le commentaire.';

        foreach ($destinataires as $user) {
            NotificationInterne::query()->create([
                'user_id' => (int) $user->id,
                'type' => $estValidation ? 'rapport_membre_valide' : 'rapport_membre_rejete',
                'title' => $title,
                'message' => $message,
                'url' => route('secretariat.rapports-membres.show', $rapport),
                'data' => ['rapport_id' => $rapport->id],
            ]);
        }
    }
}
