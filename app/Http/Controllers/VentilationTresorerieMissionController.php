<?php

namespace App\Http\Controllers;

use App\Exports\RapportDimesOffrandesMissionExport;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\NotificationInterne;
use App\Models\User;
use App\Services\Finances\VentilationTresorerieMissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VentilationTresorerieMissionController extends Controller
{
    public function __construct(
        private readonly VentilationTresorerieMissionService $ventilationTresorerieMissionService
    ) {
        $this->middleware(function (Request $request, \Closure $next) {
            if (! Gate::forUser($request->user())->allows('ventilationModule', MissionTresorerieRapportMensuel::class)) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $missionId = (int) $request->user()->mission_id;

        $rapports = MissionTresorerieRapportMensuel::query()
            ->where('mission_id', $missionId)
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->paginate(24)
            ->withQueryString();

        return view('finances.ventilation-tresorerie-mission.index', compact('rapports'));
    }

    public function edit(Request $request, int $annee, int $mois): View
    {
        $missionId = (int) $request->user()->mission_id;

        $rapport = MissionTresorerieRapportMensuel::query()->firstOrNew(
            [
                'mission_id' => $missionId,
                'annee' => $annee,
                'mois' => $mois,
            ],
            [
                'dimes_eglises' => 0,
                'autres_dimes' => 0,
                'offrandes_mois' => 0,
            ]
        );

        $this->authorize('view', $rapport);

        $suggestion = $this->ventilationTresorerieMissionService->suggererDepuisAggregat($missionId, $annee, $mois);
        if (! $request->session()->has('_old_input')) {
            // Mode simplifié: dîmes & offrandes viennent automatiquement des agrégats mission.
            $rapport->dimes_eglises = $suggestion['dimes_eglises'];
            $rapport->offrandes_mois = $suggestion['offrandes_mois'];
        }

        $rapport->loadMissing('mission.tresorerieVentilationLignes');
        $lignes = $rapport->mission->tresorerieVentilationLignes;
        $montantsParLigne = $this->ventilationTresorerieMissionService->preparerAffichage($rapport);
        $resumeTop = $this->buildResumeTop($rapport);

        return view('finances.ventilation-tresorerie-mission.edit', compact('rapport', 'lignes', 'montantsParLigne', 'annee', 'mois', 'suggestion', 'resumeTop'));
    }

    public function update(Request $request, int $annee, int $mois): RedirectResponse
    {
        $missionId = (int) $request->user()->mission_id;

        $rapport = MissionTresorerieRapportMensuel::query()->firstOrNew(
            [
                'mission_id' => $missionId,
                'annee' => $annee,
                'mois' => $mois,
            ],
            [
                'dimes_eglises' => 0,
                'autres_dimes' => 0,
                'offrandes_mois' => 0,
            ]
        );

        $this->authorize('update', $rapport);

        $validated = $request->validate([
            'dimes_eglises' => ['nullable', 'numeric', 'min:0'],
            'autres_dimes' => ['required', 'numeric', 'min:0'],
            'offrandes_mois' => ['nullable', 'numeric', 'min:0'],
        ]);

        $suggestion = $this->ventilationTresorerieMissionService->suggererDepuisAggregat($missionId, $annee, $mois);

        $rapport->fill([
            'dimes_eglises' => isset($validated['dimes_eglises']) && $validated['dimes_eglises'] !== null && $validated['dimes_eglises'] !== ''
                ? (float) $validated['dimes_eglises']
                : (float) $suggestion['dimes_eglises'],
            'autres_dimes' => (float) $validated['autres_dimes'],
            'offrandes_mois' => isset($validated['offrandes_mois']) && $validated['offrandes_mois'] !== null && $validated['offrandes_mois'] !== ''
                ? (float) $validated['offrandes_mois']
                : (float) $suggestion['offrandes_mois'],
        ]);
        $this->ventilationTresorerieMissionService->enregistrerRapport($rapport);

        return redirect()
            ->route('finances.ventilation-tresorerie-mission.edit', ['annee' => $annee, 'mois' => $mois])
            ->with('success', __('flash.ventilation_stored'));
    }

    public function soumettre(Request $request, int $annee, int $mois): RedirectResponse
    {
        $missionId = (int) $request->user()->mission_id;
        $rapport = MissionTresorerieRapportMensuel::query()->firstOrNew(
            ['mission_id' => $missionId, 'annee' => $annee, 'mois' => $mois],
            ['dimes_eglises' => 0, 'autres_dimes' => 0, 'offrandes_mois' => 0]
        );

        $this->authorize('soumettre', $rapport);

        if (! $rapport->exists) {
            $suggestion = $this->ventilationTresorerieMissionService->suggererDepuisAggregat($missionId, $annee, $mois);
            $rapport->fill([
                'dimes_eglises' => (float) $suggestion['dimes_eglises'],
                'autres_dimes' => 0.0,
                'offrandes_mois' => (float) $suggestion['offrandes_mois'],
            ]);
            $this->ventilationTresorerieMissionService->enregistrerRapport($rapport);
        }

        $rapport->update([
            'etat_transmission' => MissionTresorerieRapportMensuel::ETAT_SOUMIS,
            'soumis_le' => now(),
            'soumis_par_user_id' => $request->user()->id,
            'mission_revu_le' => null,
            'mission_revu_par_user_id' => null,
            'mission_commentaire' => null,
        ]);

        $this->notifierMissionSoumission($rapport, (int) $request->user()->id);

        return redirect()
            ->route('finances.ventilation-tresorerie-mission.edit', ['annee' => $annee, 'mois' => $mois])
            ->with('success', __('flash.ventilation_submitted'));
    }

    public function validerMission(Request $request, int $annee, int $mois): RedirectResponse
    {
        $missionId = (int) $request->user()->mission_id;
        $rapport = MissionTresorerieRapportMensuel::query()->where([
            'mission_id' => $missionId,
            'annee' => $annee,
            'mois' => $mois,
        ])->firstOrFail();

        $this->authorize('reviewMission', $rapport);

        $validated = $request->validate([
            'mission_commentaire' => ['nullable', 'string', 'max:3000'],
        ]);

        $rapport->update([
            'etat_transmission' => MissionTresorerieRapportMensuel::ETAT_VALIDE_MISSION,
            'mission_revu_le' => now(),
            'mission_revu_par_user_id' => $request->user()->id,
            'mission_commentaire' => $validated['mission_commentaire'] ?? null,
        ]);

        $this->notifierMissionRetour($rapport, true);

        return redirect()
            ->route('finances.ventilation-tresorerie-mission.edit', ['annee' => $annee, 'mois' => $mois])
            ->with('success', __('flash.ventilation_validated'));
    }

    public function refuserMission(Request $request, int $annee, int $mois): RedirectResponse
    {
        $missionId = (int) $request->user()->mission_id;
        $rapport = MissionTresorerieRapportMensuel::query()->where([
            'mission_id' => $missionId,
            'annee' => $annee,
            'mois' => $mois,
        ])->firstOrFail();

        $this->authorize('reviewMission', $rapport);

        $validated = $request->validate([
            'mission_commentaire' => ['required', 'string', 'max:3000'],
        ]);

        $rapport->update([
            'etat_transmission' => MissionTresorerieRapportMensuel::ETAT_REFUSE_MISSION,
            'mission_revu_le' => now(),
            'mission_revu_par_user_id' => $request->user()->id,
            'mission_commentaire' => $validated['mission_commentaire'],
        ]);

        $this->notifierMissionRetour($rapport, false);

        return redirect()
            ->route('finances.ventilation-tresorerie-mission.edit', ['annee' => $annee, 'mois' => $mois])
            ->with('success', __('flash.ventilation_refused'));
    }

    public function impression(Request $request, int $annee, int $mois): View
    {
        $missionId = (int) $request->user()->mission_id;

        $rapport = MissionTresorerieRapportMensuel::query()->firstOrNew(
            [
                'mission_id' => $missionId,
                'annee' => $annee,
                'mois' => $mois,
            ],
            [
                'dimes_eglises' => 0,
                'autres_dimes' => 0,
                'offrandes_mois' => 0,
            ]
        );

        $this->authorize('view', $rapport);

        $rapport->loadMissing('mission.tresorerieVentilationLignes');
        $lignes = $rapport->mission->tresorerieVentilationLignes;
        $montantsParLigne = $this->ventilationTresorerieMissionService->preparerAffichage($rapport);

        return view('finances.ventilation-tresorerie-mission.impression', compact('rapport', 'lignes', 'montantsParLigne', 'annee', 'mois'));
    }

    public function exportPdf(Request $request, int $annee, int $mois): Response
    {
        $missionId = (int) $request->user()->mission_id;
        $rapport = MissionTresorerieRapportMensuel::query()->firstOrNew(
            ['mission_id' => $missionId, 'annee' => $annee, 'mois' => $mois],
            ['dimes_eglises' => 0, 'autres_dimes' => 0, 'offrandes_mois' => 0]
        );
        $this->authorize('view', $rapport);

        $rapport->loadMissing('mission.tresorerieVentilationLignes');
        $lignes = $rapport->mission->tresorerieVentilationLignes;
        $montantsParLigne = $this->ventilationTresorerieMissionService->preparerAffichage($rapport);

        return Pdf::loadView('finances.ventilation-tresorerie-mission.impression', compact('rapport', 'lignes', 'montantsParLigne', 'annee', 'mois'))
            ->setPaper('a4', 'landscape')
            ->download('rapport-dimes-offrandes-'.$annee.'-'.$mois.'.pdf');
    }

    public function exportExcel(Request $request, int $annee, int $mois): BinaryFileResponse
    {
        $missionId = (int) $request->user()->mission_id;
        $rapport = MissionTresorerieRapportMensuel::query()->firstOrNew(
            ['mission_id' => $missionId, 'annee' => $annee, 'mois' => $mois],
            ['dimes_eglises' => 0, 'autres_dimes' => 0, 'offrandes_mois' => 0]
        );
        $this->authorize('view', $rapport);

        $prevAnnee = $mois > 1 ? $annee : $annee - 1;
        $prevMois = $mois > 1 ? $mois - 1 : 12;
        $rapportPrecedent = MissionTresorerieRapportMensuel::query()
            ->where('mission_id', $missionId)
            ->where('annee', $prevAnnee)
            ->where('mois', $prevMois)
            ->first();
        $prevDimesEglises = (float) ($rapportPrecedent?->dimes_eglises ?? 0);
        $prevAutresDimes = (float) ($rapportPrecedent?->autres_dimes ?? 0);
        $prevTotalDimes = round($prevDimesEglises + $prevAutresDimes, 2);
        $prevOffrandes = (float) ($rapportPrecedent?->offrandes_mois ?? 0);
        $prevTotalRecettes = round($prevTotalDimes + $prevOffrandes, 2);

        $dimesMois = (float) $rapport->totalDimesMois();
        $dimesPrev = $prevTotalDimes;
        $offrandesMois = (float) $rapport->offrandes_mois;
        $offrandesPrev = $prevOffrandes;

        $dimeUnionMois = round($dimesMois * 0.08, 2);
        $dimeUnionPrev = round($dimesPrev * 0.08, 2);

        $fondsCgMois = round($dimesMois * 0.026, 2);
        $fondsCgPrev = round($dimesPrev * 0.026, 2);

        $fondsDaoInstMois = round($dimesMois * 0.02, 2);
        $fondsDaoInstPrev = round($dimesPrev * 0.02, 2);

        $fondsRetraitesMois = round($dimesMois * 0.12, 2);
        $fondsRetraitesPrev = round($dimesPrev * 0.12, 2);

        $fondsDimePartageeMois = round($dimesMois * 0.074, 2);
        $fondsDimePartageePrev = round($dimesPrev * 0.074, 2);

        $totalPctDimeMois = round($fondsCgMois + $fondsDaoInstMois + $fondsRetraitesMois + $fondsDimePartageeMois, 2);
        $totalPctDimePrev = round($fondsCgPrev + $fondsDaoInstPrev + $fondsRetraitesPrev + $fondsDimePartageePrev, 2);

        $offCgMois = round($offrandesMois * 0.20, 2);
        $offCgPrev = round($offrandesPrev * 0.20, 2);
        $offDaoMois = round($offrandesMois * 0.05, 2);
        $offDaoPrev = round($offrandesPrev * 0.05, 2);
        $offUmacMois = round($offrandesMois * 0.05, 2);
        $offUmacPrev = round($offrandesPrev * 0.05, 2);
        $totalOffHautMois = round($offCgMois + $offDaoMois + $offUmacMois, 2);
        $totalOffHautPrev = round($offCgPrev + $offDaoPrev + $offUmacPrev, 2);

        $totalRapportMois = round($dimeUnionMois + $totalPctDimeMois + $totalOffHautMois, 2);
        $totalRapportPrev = round($dimeUnionPrev + $totalPctDimePrev + $totalOffHautPrev, 2);

        $offMissionMois = round($offrandesMois * 0.20, 2);
        $offMissionPrev = round($offrandesPrev * 0.20, 2);
        $offLocaleMois = round($offrandesMois * 0.50, 2);
        $offLocalePrev = round($offrandesPrev * 0.50, 2);

        $rows = [
            ['DIMES DES EGLISES', '', (float) $rapport->dimes_eglises, $prevDimesEglises, round($prevDimesEglises + (float) $rapport->dimes_eglises, 2)],
            ['AUTRES DIMES', '', (float) $rapport->autres_dimes, $prevAutresDimes, round($prevAutresDimes + (float) $rapport->autres_dimes, 2)],
            ['TOTAL RECETTES DIMES DU MOIS', '', (float) $rapport->totalDimesMois(), $prevTotalDimes, round($prevTotalDimes + (float) $rapport->totalDimesMois(), 2)],
            ['TOTAL OFFRANDES DU MOIS', '', (float) $rapport->offrandes_mois, $prevOffrandes, round($prevOffrandes + (float) $rapport->offrandes_mois, 2)],
            ['TOTAL REVENUS DU MOIS', '', (float) $rapport->totalRecettesMois(), $prevTotalRecettes, round($prevTotalRecettes + (float) $rapport->totalRecettesMois(), 2)],
            ['DIME DE LA DIME (UNION)', '8%', $dimeUnionMois, $dimeUnionPrev, round($dimeUnionPrev + $dimeUnionMois, 2)],
            ['POURCENTAGE DE LA DIME', '', '', '', ''],
            ['FONDS CONF. GENERALE', '2,6%', $fondsCgMois, $fondsCgPrev, round($fondsCgPrev + $fondsCgMois, 2)],
            ['FONDS INSTITUTIONS DAO', '2,0%', $fondsDaoInstMois, $fondsDaoInstPrev, round($fondsDaoInstPrev + $fondsDaoInstMois, 2)],
            ['FONDS DE RETRAITES DAO', '12,0%', $fondsRetraitesMois, $fondsRetraitesPrev, round($fondsRetraitesPrev + $fondsRetraitesMois, 2)],
            ['FONDS DIME PARTAGEE DAO', '7,4%', $fondsDimePartageeMois, $fondsDimePartageePrev, round($fondsDimePartageePrev + $fondsDimePartageeMois, 2)],
            ['TOTAL POURCENTAGE DE DIME >>>>>>>', '', $totalPctDimeMois, $totalPctDimePrev, round($totalPctDimePrev + $totalPctDimeMois, 2)],
            ['REPARTITION OFFRANDE', '', '', '', ''],
            ['CONF.GENERALE / DIVISION', '', '', '', ''],
            ['FONDS CHAMPS MONDIALE - CG', '20%', $offCgMois, $offCgPrev, round($offCgPrev + $offCgMois, 2)],
            ['FONDS OFFRANDE - DAO', '5%', $offDaoMois, $offDaoPrev, round($offDaoPrev + $offDaoMois, 2)],
            ["UNION MISSION DE L'AFRIQUE CENTRALE", '', '', '', ''],
            ['FONDS OFFRANDE UMAC', '5%', $offUmacMois, $offUmacPrev, round($offUmacPrev + $offUmacMois, 2)],
            ['TOTAL OFFRANDE (CG, DAO & UMAC) >>>>>>>', '', $totalOffHautMois, $totalOffHautPrev, round($totalOffHautPrev + $totalOffHautMois, 2)],
            ['TOTAL RAPPORT (GC-DAO-UMAC)', '', $totalRapportMois, $totalRapportPrev, round($totalRapportPrev + $totalRapportMois, 2)],
            ['MISSION / FEDERATION', '', '', '', ''],
            ['REPARTITION OFFRANDE', '20%', $offMissionMois, $offMissionPrev, round($offMissionPrev + $offMissionMois, 2)],
            ['OFFRANDE SPEC./PROJET', '', '', '', ''],
            ['TOTAL FONDS MISSION', '', $offMissionMois, $offMissionPrev, round($offMissionPrev + $offMissionMois, 2)],
            ['EGLISE LOCALE', '', '', '', ''],
            ['REPARTITION OFFRANDE', '50%', $offLocaleMois, $offLocalePrev, round($offLocalePrev + $offLocaleMois, 2)],
            ['OFFRANDE SPEC./PROJET', '', '', '', ''],
            ['FONDS CONSTRUCTION EGLISE LOCALE', '', '', '', ''],
            ['TOTAL FONDS EGLISE LOCALE', '', $offLocaleMois, $offLocalePrev, round($offLocalePrev + $offLocaleMois, 2)],
            ['AUTRES DIMES', '', '', '', ''],
            ['DIMES OUVRIERS DE BUREAU', '', (float) $rapport->autres_dimes, $prevAutresDimes, round($prevAutresDimes + (float) $rapport->autres_dimes, 2)],
            ['DIMES OUVRIERS GOC ET GOB', '', '-', '-', '-'],
            ['DIMES PIONNIERS MISSI. GLOB.', '', '-', '-', '-'],
            ['DIMES RE & LIBRAIRIE', '', '-', '-', '-'],
            ['DIMES SPECIALES', '', '-', '-', '-'],
            ['DIMES ECOLES & COLLEGES', '', '-', '-', '-'],
            ['TOTAL AUTRES DIMES >>>>>>>', '', (float) $rapport->autres_dimes, $prevAutresDimes, round($prevAutresDimes + (float) $rapport->autres_dimes, 2)],
        ];

        return Excel::download(
            new RapportDimesOffrandesMissionExport(
                (string) ($rapport->mission?->nom ?? 'MISSION'),
                $annee,
                $mois,
                $rows
            ),
            'rapport-dimes-offrandes-'.$annee.'-'.$mois.'.xlsx'
        );
    }

    private function notifierMissionSoumission(MissionTresorerieRapportMensuel $rapport, int $emetteurUserId): void
    {
        $missionId = (int) $rapport->mission_id;
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
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['admin_mission', 'president_mission']))
            ->get(['id']);

        foreach ($users as $user) {
            NotificationInterne::query()->create([
                'user_id' => (int) $user->id,
                'type' => 'ventilation_mission_soumise',
                'title' => 'Rapport de ventilation soumis',
                'message' => 'Le rapport de ventilation '.$periode.' a été soumis pour validation.',
                'url' => route('finances.ventilation-tresorerie-mission.edit', ['annee' => $rapport->annee, 'mois' => $rapport->mois]),
                'data' => ['mission_tresorerie_rapport_id' => $rapport->id],
            ]);
        }
    }

    private function notifierMissionRetour(MissionTresorerieRapportMensuel $rapport, bool $valide): void
    {
        $users = User::query()
            ->where('mission_id', (int) $rapport->mission_id)
            ->whereNull('eglise_locale_id')
            ->whereHas('role', fn ($q) => $q->whereIn('name', ['tresorier_mission', 'admin_mission']))
            ->get(['id']);

        $title = $valide ? 'Rapport de ventilation validé' : 'Rapport de ventilation refusé';
        $message = $valide
            ? 'Votre rapport de ventilation a été validé.'
            : 'Votre rapport de ventilation a été refusé. Consultez le commentaire.';

        foreach ($users as $user) {
            NotificationInterne::query()->create([
                'user_id' => (int) $user->id,
                'type' => $valide ? 'ventilation_mission_validee' : 'ventilation_mission_refusee',
                'title' => $title,
                'message' => $message,
                'url' => route('finances.ventilation-tresorerie-mission.edit', ['annee' => $rapport->annee, 'mois' => $rapport->mois]),
                'data' => ['mission_tresorerie_rapport_id' => $rapport->id],
            ]);
        }
    }

    /**
     * @return array{
     *   dimes_eglises: array{mois: float, precedent: float, cumule: float},
     *   autres_dimes: array{mois: float, precedent: float, cumule: float},
     *   total_dimes: array{mois: float, precedent: float, cumule: float},
     *   offrandes_mois: array{mois: float, precedent: float, cumule: float},
     *   total_recettes: array{mois: float, precedent: float, cumule: float}
     * }
     */
    private function buildResumeTop(MissionTresorerieRapportMensuel $rapport): array
    {
        $annee = (int) $rapport->annee;
        $mois = (int) $rapport->mois;
        $missionId = (int) $rapport->mission_id;
        $prevAnnee = $mois > 1 ? $annee : $annee - 1;
        $prevMois = $mois > 1 ? $mois - 1 : 12;

        $prev = MissionTresorerieRapportMensuel::query()
            ->where('mission_id', $missionId)
            ->where('annee', $prevAnnee)
            ->where('mois', $prevMois)
            ->first();

        $moisDimesEglises = (float) $rapport->dimes_eglises;
        $moisAutresDimes = (float) $rapport->autres_dimes;
        $moisTotalDimes = (float) $rapport->totalDimesMois();
        $moisOffrandes = (float) $rapport->offrandes_mois;
        $moisTotalRecettes = (float) $rapport->totalRecettesMois();

        $prevDimesEglises = (float) ($prev?->dimes_eglises ?? 0);
        $prevAutresDimes = (float) ($prev?->autres_dimes ?? 0);
        $prevTotalDimes = round($prevDimesEglises + $prevAutresDimes, 2);
        $prevOffrandes = (float) ($prev?->offrandes_mois ?? 0);
        $prevTotalRecettes = round($prevTotalDimes + $prevOffrandes, 2);

        return [
            'dimes_eglises' => [
                'mois' => $moisDimesEglises,
                'precedent' => $prevDimesEglises,
                'cumule' => round($prevDimesEglises + $moisDimesEglises, 2),
            ],
            'autres_dimes' => [
                'mois' => $moisAutresDimes,
                'precedent' => $prevAutresDimes,
                'cumule' => round($prevAutresDimes + $moisAutresDimes, 2),
            ],
            'total_dimes' => [
                'mois' => $moisTotalDimes,
                'precedent' => $prevTotalDimes,
                'cumule' => round($prevTotalDimes + $moisTotalDimes, 2),
            ],
            'offrandes_mois' => [
                'mois' => $moisOffrandes,
                'precedent' => $prevOffrandes,
                'cumule' => round($prevOffrandes + $moisOffrandes, 2),
            ],
            'total_recettes' => [
                'mois' => $moisTotalRecettes,
                'precedent' => $prevTotalRecettes,
                'cumule' => round($prevTotalRecettes + $moisTotalRecettes, 2),
            ],
        ];
    }
}
