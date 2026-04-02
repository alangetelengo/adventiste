<?php

namespace App\Http\Controllers\Finances;

use App\Exports\EtatDimesEglisesMissionExport;
use App\Http\Controllers\Controller;
use App\Models\EgliseLocale;
use App\Models\Membre;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\RapportMensuelEglise;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EtatDimesEglisesMissionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MissionTresorerieRapportMensuel::class);

        $annee = (int) $request->integer('annee', (int) now()->year);
        $missionId = (int) $request->user()->mission_id;

        $lignes = $this->construireLignes($missionId, $annee);
        $totaux = $this->construireTotaux($lignes);

        return view('finances.etat-dimes-eglises.index', [
            'annee' => $annee,
            'lignes' => $lignes,
            'totaux' => $totaux,
        ]);
    }

    public function impression(Request $request): View
    {
        $this->authorize('viewAny', MissionTresorerieRapportMensuel::class);

        $annee = (int) $request->integer('annee', (int) now()->year);
        $missionId = (int) $request->user()->mission_id;

        $lignes = $this->construireLignes($missionId, $annee);
        $totaux = $this->construireTotaux($lignes);

        return view('finances.etat-dimes-eglises.impression', [
            'annee' => $annee,
            'lignes' => $lignes,
            'totaux' => $totaux,
            'missionNom' => (string) ($request->user()->mission?->nom ?? 'Mission'),
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', MissionTresorerieRapportMensuel::class);

        $annee = (int) $request->integer('annee', (int) now()->year);
        $missionId = (int) $request->user()->mission_id;
        $lignes = $this->construireLignes($missionId, $annee);
        $totaux = $this->construireTotaux($lignes);
        $missionNom = (string) ($request->user()->mission?->nom ?? 'Mission');

        return Pdf::loadView('finances.etat-dimes-eglises.impression', compact('annee', 'lignes', 'totaux', 'missionNom'))
            ->setPaper('a4', 'landscape')
            ->download('etat-dimes-eglises-'.$annee.'.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', MissionTresorerieRapportMensuel::class);

        $annee = (int) $request->integer('annee', (int) now()->year);
        $missionId = (int) $request->user()->mission_id;
        $lignes = $this->construireLignes($missionId, $annee);
        $totaux = $this->construireTotaux($lignes);

        $rows = [];
        foreach ($lignes as $ligne) {
            $rows[] = [
                $ligne['rang'],
                $ligne['eglise_nom'],
                (float) $ligne['objectif_dimes'],
                (float) $ligne['dimes_collectees'],
                (float) $ligne['offrandes_collectees'],
                (float) $ligne['dimes_annee_precedente'],
                (float) $ligne['ecart_dimes'],
                (float) $ligne['dimes_moyenne_mensuelle'],
                $ligne['nombre_membres'],
                (float) $ligne['pourcentage_apport'],
            ];
        }
        $rows[] = [
            '',
            'TOTAL',
            (float) $totaux['objectif_dimes'],
            (float) $totaux['dimes_collectees'],
            (float) $totaux['offrandes_collectees'],
            (float) $totaux['dimes_annee_precedente'],
            (float) $totaux['ecart_dimes'],
            (float) $totaux['dimes_moyenne_mensuelle'],
            $totaux['nombre_membres'],
            (float) $totaux['pourcentage_apport'],
        ];

        return Excel::download(
            new EtatDimesEglisesMissionExport(
                (string) ($request->user()->mission?->nom ?? 'MISSION'),
                $annee,
                $rows
            ),
            'etat-dimes-eglises-'.$annee.'.xlsx'
        );
    }

    /**
     * @return Collection<int, array{
     *   rang:int,
     *   eglise_id:int,
     *   eglise_nom:string,
     *   objectif_dimes:float,
     *   dimes_collectees:float,
     *   offrandes_collectees:float,
     *   dimes_annee_precedente:float,
     *   ecart_dimes:float,
     *   dimes_moyenne_mensuelle:float,
     *   nombre_membres:int,
     *   pourcentage_apport:float
     * }>
     */
    private function construireLignes(int $missionId, int $annee): Collection
    {
        $eglises = EgliseLocale::query()
            ->where('mission_id', $missionId)
            ->where('actif', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'indicateurs_financiers']);

        $dimesParEglise = RapportMensuelEglise::query()
            ->selectRaw('eglise_locale_id, COALESCE(SUM(total_dimes_mois), 0) as total_dimes')
            ->where('annee', $annee)
            ->whereIn('etat_transmission', [
                RapportMensuelEglise::ETAT_SOUMIS,
                RapportMensuelEglise::ETAT_VALIDE_MISSION,
            ])
            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
            ->groupBy('eglise_locale_id')
            ->pluck('total_dimes', 'eglise_locale_id');

        $offrandesParEglise = RapportMensuelEglise::query()
            ->selectRaw('eglise_locale_id, COALESCE(SUM(total_moitie_offrandes_mois + total_autres_offrandes_mission_mois), 0) as total_offrandes')
            ->where('annee', $annee)
            ->whereIn('etat_transmission', [
                RapportMensuelEglise::ETAT_SOUMIS,
                RapportMensuelEglise::ETAT_VALIDE_MISSION,
            ])
            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
            ->groupBy('eglise_locale_id')
            ->pluck('total_offrandes', 'eglise_locale_id');

        $membresParEglise = Membre::query()
            ->selectRaw('eglise_locale_id, COUNT(*) as total_membres')
            ->where('actif', true)
            ->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
            ->groupBy('eglise_locale_id')
            ->pluck('total_membres', 'eglise_locale_id');

        $rows = [];
        foreach ($eglises as $index => $eglise) {
            $indicateurs = is_array($eglise->indicateurs_financiers) ? $eglise->indicateurs_financiers : [];
            $objectif = (float) ($indicateurs['objectif_dimes'] ?? 0);
            $dimesAnneePrecedente = (float) ($indicateurs['dimes_annee_precedente'] ?? 0);
            $moyenneIndicateur = (float) ($indicateurs['dimes_moyenne_mensuelle'] ?? 0);

            $dimesCollectees = round((float) ($dimesParEglise[$eglise->id] ?? 0), 2);
            $offrandesCollectees = round((float) ($offrandesParEglise[$eglise->id] ?? 0), 2);
            $moyenneCalculee = round($dimesCollectees / 12, 2);
            $moyenneMensuelle = $moyenneIndicateur > 0 ? $moyenneIndicateur : $moyenneCalculee;
            $membres = (int) ($membresParEglise[$eglise->id] ?? 0);
            $pourcentageApport = $objectif > 0 ? round(($dimesCollectees / $objectif) * 100, 2) : 0.0;
            $ecartDimes = round($dimesCollectees - $dimesAnneePrecedente, 2);

            $rows[] = [
                'rang' => $index + 1,
                'eglise_id' => (int) $eglise->id,
                'eglise_nom' => (string) $eglise->nom,
                'objectif_dimes' => round($objectif, 2),
                'dimes_collectees' => $dimesCollectees,
                'offrandes_collectees' => $offrandesCollectees,
                'dimes_annee_precedente' => round($dimesAnneePrecedente, 2),
                'ecart_dimes' => $ecartDimes,
                'dimes_moyenne_mensuelle' => round($moyenneMensuelle, 2),
                'nombre_membres' => $membres,
                'pourcentage_apport' => $pourcentageApport,
            ];
        }

        return collect($rows);
    }

    /**
     * @param  Collection<int, array{
     *   objectif_dimes:float,
     *   dimes_collectees:float,
     *   offrandes_collectees:float,
     *   dimes_annee_precedente:float,
     *   ecart_dimes:float,
     *   dimes_moyenne_mensuelle:float,
     *   nombre_membres:int
     * }>  $lignes
     * @return array{
     *   objectif_dimes:float,
     *   dimes_collectees:float,
     *   offrandes_collectees:float,
     *   dimes_annee_precedente:float,
     *   ecart_dimes:float,
     *   dimes_moyenne_mensuelle:float,
     *   nombre_membres:int,
     *   pourcentage_apport:float
     * }
     */
    private function construireTotaux(Collection $lignes): array
    {
        $objectif = round((float) $lignes->sum('objectif_dimes'), 2);
        $dimes = round((float) $lignes->sum('dimes_collectees'), 2);
        $offrandes = round((float) $lignes->sum('offrandes_collectees'), 2);
        $dimesPrev = round((float) $lignes->sum('dimes_annee_precedente'), 2);
        $ecartDimes = round((float) $lignes->sum('ecart_dimes'), 2);
        $moyenne = round((float) $lignes->sum('dimes_moyenne_mensuelle'), 2);
        $membres = (int) $lignes->sum('nombre_membres');
        $pourcentage = $objectif > 0 ? round(($dimes / $objectif) * 100, 2) : 0.0;

        return [
            'objectif_dimes' => $objectif,
            'dimes_collectees' => $dimes,
            'offrandes_collectees' => $offrandes,
            'dimes_annee_precedente' => $dimesPrev,
            'ecart_dimes' => $ecartDimes,
            'dimes_moyenne_mensuelle' => $moyenne,
            'nombre_membres' => $membres,
            'pourcentage_apport' => $pourcentage,
        ];
    }
}
