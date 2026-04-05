<?php

namespace App\Http\Controllers;

use App\Models\NotificationInterne;
use App\Models\RapportMensuelEglise;
use App\Models\User;
use App\Services\Finances\RapportMensuelSyntheseService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RapportMensuelEgliseController extends Controller
{
    public function __construct(
        private readonly RapportMensuelSyntheseService $rapportMensuelSyntheseService
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', RapportMensuelEglise::class);

        $user = $request->user();
        $query = RapportMensuelEglise::query()
            ->with(['egliseLocale', 'soumisPar', 'missionRevuPar'])
            ->orderByDesc('annee')
            ->orderByDesc('mois');

        $egliseFiltre = null;

        if ($user->eglise_locale_id) {
            $query->where('eglise_locale_id', $user->eglise_locale_id);
            $egliseFiltre = $user->egliseLocale;
        } elseif ($user->mission_id) {
            $query->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $user->mission_id));
        }

        if ($request->filled('etat_transmission')) {
            $query->where('etat_transmission', (string) $request->input('etat_transmission'));
        }

        $rapports = $query->paginate(20)->withQueryString();
        $etatsTransmission = RapportMensuelEglise::labelsEtatsTransmission();

        return view('finances.rapports-mensuels.index', compact('rapports', 'egliseFiltre', 'etatsTransmission'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RapportMensuelEglise::class);

        return view('finances.rapports-mensuels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RapportMensuelEglise::class);

        $eglise = $request->user()->egliseLocale;
        abort_unless($eglise !== null, 403);

        $validated = $request->validate([
            'annee' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mois' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $existant = RapportMensuelEglise::query()
            ->where('eglise_locale_id', $eglise->id)
            ->where('annee', $validated['annee'])
            ->where('mois', $validated['mois'])
            ->first();

        if ($existant !== null) {
            return redirect()
                ->route('finances.rapports-mensuels.show', $existant)
                ->with('info', __('flash.rapport_mensuel_exists'));
        }

        $rapport = $this->rapportMensuelSyntheseService->regenererPourEgliseEtMois(
            $eglise,
            $validated['annee'],
            $validated['mois']
        );

        return redirect()
            ->route('finances.rapports-mensuels.show', $rapport)
            ->with('success', __('flash.rapport_mensuel_generated'));
    }

    public function show(Request $request, RapportMensuelEglise $rapport): View
    {
        $this->authorize('view', $rapport);

        $rapport->load(['egliseLocale', 'lignesSabbat', 'lignesSynthese']);
        $rapport->load(['soumisPar', 'missionRevuPar']);
        $resumeComparatif = $this->buildResumeComparatif($rapport);

        return view('finances.rapports-mensuels.show', compact('rapport', 'resumeComparatif'));
    }

    public function edit(Request $request, RapportMensuelEglise $rapport): View|RedirectResponse
    {
        $this->authorize('update', $rapport);

        if ($rapport->verrouille_le !== null) {
            return redirect()
                ->route('finances.rapports-mensuels.show', $rapport)
                ->with('info', __('flash.rapport_mensuel_locked_signatures'));
        }

        return view('finances.rapports-mensuels.edit', compact('rapport'));
    }

    public function update(Request $request, RapportMensuelEglise $rapport): RedirectResponse
    {
        $this->authorize('update', $rapport);

        if ($rapport->verrouille_le !== null) {
            return redirect()
                ->route('finances.rapports-mensuels.show', $rapport)
                ->with('error', __('flash.rapport_mensuel_locked'));
        }

        $validated = $request->validate([
            'date_signature_tresorier' => ['nullable', 'date'],
            'date_signature_pasteur' => ['nullable', 'date'],
            'verrouiller' => ['nullable', Rule::in(['0', '1'])],
        ]);

        $rapport->update([
            'signe_tresorier' => $request->boolean('signe_tresorier'),
            'date_signature_tresorier' => $validated['date_signature_tresorier'] ?? null,
            'signe_pasteur' => $request->boolean('signe_pasteur'),
            'date_signature_pasteur' => $validated['date_signature_pasteur'] ?? null,
            'signe_secretaire' => false,
            'date_signature_secretaire' => null,
            'etabli_a' => null,
            'etabli_le' => null,
            'verrouille_le' => ($rapport->verrouille_le === null && ($validated['verrouiller'] ?? '0') === '1')
                ? now()
                : $rapport->verrouille_le,
        ]);

        return redirect()
            ->route('finances.rapports-mensuels.show', $rapport)
            ->with('success', __('flash.rapport_mensuel_saved'));
    }

    public function destroy(Request $request, RapportMensuelEglise $rapport): RedirectResponse
    {
        $this->authorize('delete', $rapport);

        if ($rapport->verrouille_le !== null) {
            return redirect()
                ->route('finances.rapports-mensuels.index')
                ->with('error', __('flash.rapport_mensuel_delete_locked'));
        }

        $rapport->delete();

        return redirect()
            ->route('finances.rapports-mensuels.index')
            ->with('success', __('flash.rapport_mensuel_deleted'));
    }

    public function regenerer(Request $request, RapportMensuelEglise $rapport): RedirectResponse
    {
        $this->authorize('update', $rapport);

        if ($rapport->verrouille_le !== null) {
            return redirect()
                ->route('finances.rapports-mensuels.show', $rapport)
                ->with('error', __('flash.rapport_mensuel_regen_locked'));
        }

        $eglise = $rapport->egliseLocale;
        $this->rapportMensuelSyntheseService->regenererPourEgliseEtMois($eglise, (int) $rapport->annee, (int) $rapport->mois);
        $rapport->refresh();

        return redirect()
            ->route('finances.rapports-mensuels.show', $rapport)
            ->with('success', __('flash.rapport_mensuel_regenerated'));
    }

    public function soumettre(Request $request, RapportMensuelEglise $rapport): RedirectResponse
    {
        $this->authorize('soumettre', $rapport);

        $rapport->update([
            'etat_transmission' => RapportMensuelEglise::ETAT_SOUMIS,
            'soumis_le' => now(),
            'soumis_par_user_id' => $request->user()->id,
            'mission_revu_le' => null,
            'mission_revu_par_user_id' => null,
            'mission_commentaire' => null,
        ]);

        $this->notifierMissionSoumission($rapport, (int) $request->user()->id);

        return redirect()
            ->route('finances.rapports-mensuels.show', $rapport)
            ->with('success', __('flash.rapport_mensuel_submitted'));
    }

    public function validerMission(Request $request, RapportMensuelEglise $rapport): RedirectResponse
    {
        $this->authorize('reviewMission', $rapport);

        $validated = $request->validate([
            'mission_commentaire' => ['nullable', 'string', 'max:3000'],
        ]);

        $rapport->update([
            'etat_transmission' => RapportMensuelEglise::ETAT_VALIDE_MISSION,
            'mission_revu_le' => now(),
            'mission_revu_par_user_id' => $request->user()->id,
            'mission_commentaire' => $validated['mission_commentaire'] ?? null,
        ]);

        $this->notifierEgliseRetour($rapport, true);

        return redirect()
            ->route('finances.rapports-mensuels.show', $rapport)
            ->with('success', __('flash.rapport_mensuel_validated'));
    }

    public function refuserMission(Request $request, RapportMensuelEglise $rapport): RedirectResponse
    {
        $this->authorize('reviewMission', $rapport);

        $validated = $request->validate([
            'mission_commentaire' => ['required', 'string', 'max:3000'],
        ]);

        $rapport->update([
            'etat_transmission' => RapportMensuelEglise::ETAT_REFUSE_MISSION,
            'mission_revu_le' => now(),
            'mission_revu_par_user_id' => $request->user()->id,
            'mission_commentaire' => $validated['mission_commentaire'],
        ]);

        $this->notifierEgliseRetour($rapport, false);

        return redirect()
            ->route('finances.rapports-mensuels.show', $rapport)
            ->with('success', __('flash.rapport_mensuel_refused'));
    }

    private function notifierMissionSoumission(RapportMensuelEglise $rapport, int $emetteurUserId): void
    {
        $missionId = (int) $rapport->egliseLocale->mission_id;
        if ($missionId <= 0) {
            return;
        }
        $periode = Carbon::createFromDate((int) $rapport->annee, (int) $rapport->mois, 1)
            ->locale(app()->getLocale())
            ->translatedFormat('F/Y');

        $users = User::query()
            ->where('mission_id', $missionId)
            ->whereNull('eglise_locale_id')
            ->where('id', '<>', $emetteurUserId)
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['tresorier_mission', 'admin_mission']))
            ->get(['id']);

        foreach ($users as $user) {
            NotificationInterne::query()->create([
                'user_id' => (int) $user->id,
                'type' => 'rapport_finance_soumis',
                'title' => 'Nouveau rapport mensuel soumis',
                'message' => 'L’église '.$rapport->egliseLocale->nom.' a soumis son rapport mensuel '.$periode.'.',
                'url' => route('finances.rapports-mensuels.show', $rapport),
                'data' => ['rapport_mensuel_eglise_id' => $rapport->id],
            ]);
        }
    }

    private function notifierEgliseRetour(RapportMensuelEglise $rapport, bool $valide): void
    {
        $users = User::query()
            ->where('eglise_locale_id', (int) $rapport->eglise_locale_id)
            ->whereHas('role', fn ($q) => $q->where('name', 'tresorier_eglise'))
            ->get(['id']);

        $title = $valide ? 'Rapport mensuel validé' : 'Rapport mensuel refusé';
        $message = $valide
            ? 'La mission a validé votre rapport mensuel.'
            : 'La mission a refusé votre rapport mensuel. Consultez le commentaire.';

        foreach ($users as $user) {
            NotificationInterne::query()->create([
                'user_id' => (int) $user->id,
                'type' => $valide ? 'rapport_finance_valide' : 'rapport_finance_refuse',
                'title' => $title,
                'message' => $message,
                'url' => route('finances.rapports-mensuels.show', $rapport),
                'data' => ['rapport_mensuel_eglise_id' => $rapport->id],
            ]);
        }
    }

    /**
     * @return array{
     *   dimes:array{mois:float,precedent:float,cumule:float},
     *   moitie_offrandes:array{mois:float,precedent:float,cumule:float},
     *   autres_offrandes:array{mois:float,precedent:float,cumule:float},
     *   transferer_mission:array{mois:float,precedent:float,cumule:float}
     * }
     */
    private function buildResumeComparatif(RapportMensuelEglise $rapport): array
    {
        $annee = (int) $rapport->annee;
        $mois = (int) $rapport->mois;
        $egliseId = (int) $rapport->eglise_locale_id;
        $prevAnnee = $mois > 1 ? $annee : $annee - 1;
        $prevMois = $mois > 1 ? $mois - 1 : 12;

        $precedent = RapportMensuelEglise::query()
            ->where('eglise_locale_id', $egliseId)
            ->where('annee', $prevAnnee)
            ->where('mois', $prevMois)
            ->first([
                'total_dimes_mois',
                'total_moitie_offrandes_mois',
                'total_autres_offrandes_mission_mois',
                'total_a_transferer_mission_mois',
            ]);

        $moisDimes = (float) $rapport->total_dimes_mois;
        $moisMoitie = (float) $rapport->total_moitie_offrandes_mois;
        $moisAutres = (float) $rapport->total_autres_offrandes_mission_mois;
        $moisTransferer = (float) $rapport->total_a_transferer_mission_mois;

        $prevDimes = (float) ($precedent?->total_dimes_mois ?? 0);
        $prevMoitie = (float) ($precedent?->total_moitie_offrandes_mois ?? 0);
        $prevAutres = (float) ($precedent?->total_autres_offrandes_mission_mois ?? 0);
        $prevTransferer = (float) ($precedent?->total_a_transferer_mission_mois ?? 0);

        return [
            'dimes' => [
                'mois' => $moisDimes,
                'precedent' => $prevDimes,
                'cumule' => round($prevDimes + $moisDimes, 2),
            ],
            'moitie_offrandes' => [
                'mois' => $moisMoitie,
                'precedent' => $prevMoitie,
                'cumule' => round($prevMoitie + $moisMoitie, 2),
            ],
            'autres_offrandes' => [
                'mois' => $moisAutres,
                'precedent' => $prevAutres,
                'cumule' => round($prevAutres + $moisAutres, 2),
            ],
            'transferer_mission' => [
                'mois' => $moisTransferer,
                'precedent' => $prevTransferer,
                'cumule' => round($prevTransferer + $moisTransferer, 2),
            ],
        ];
    }
}
