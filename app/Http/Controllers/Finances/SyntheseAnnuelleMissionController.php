<?php

namespace App\Http\Controllers\Finances;

use App\Exports\SyntheseAnnuelleMissionExport;
use App\Http\Controllers\Controller;
use App\Models\AggregatSyntheseMissionMensuelle;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\MissionTresorerieTransfertBancaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SyntheseAnnuelleMissionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MissionTresorerieRapportMensuel::class);

        $annee = (int) $request->integer('annee', (int) now()->year);
        $missionId = (int) $request->user()->mission_id;

        $data = $this->construireSynthese($missionId, $annee);
        $peutSaisir = $this->peutSaisir($request, $missionId, $annee);

        return view('finances.synthese-annuelle-mission.index', [
            'annee' => $annee,
            'nomsMois' => $this->nomsMois(),
            'synthese' => $data,
            'peutSaisir' => $peutSaisir,
        ]);
    }

    public function update(Request $request, int $annee): RedirectResponse
    {
        $missionId = (int) $request->user()->mission_id;
        $this->authorize('update', new MissionTresorerieRapportMensuel([
            'mission_id' => $missionId,
            'annee' => $annee,
            'mois' => 1,
        ]));

        if (! Schema::hasTable('mission_tresorerie_transferts_bancaires')) {
            return redirect()
                ->route('finances.synthese-annuelle-mission.index', ['annee' => $annee])
                ->with('error', __('flash.synthese_transfers_table_missing'));
        }

        $validated = $request->validate([
            'transferts' => ['required', 'array'],
            'transferts.*.montant_transfere' => ['nullable', 'numeric', 'min:0'],
            'transferts.*.observation' => ['nullable', 'string', 'max:500'],
        ]);

        for ($mois = 1; $mois <= 12; $mois++) {
            $row = $validated['transferts'][(string) $mois] ?? $validated['transferts'][$mois] ?? [];
            $montant = isset($row['montant_transfere']) && $row['montant_transfere'] !== ''
                ? (float) $row['montant_transfere']
                : 0.0;
            $observation = isset($row['observation']) && trim((string) $row['observation']) !== ''
                ? trim((string) $row['observation'])
                : null;

            MissionTresorerieTransfertBancaire::query()->updateOrCreate(
                [
                    'mission_id' => $missionId,
                    'annee' => $annee,
                    'mois' => $mois,
                ],
                [
                    'montant_transfere' => round($montant, 2),
                    'observation' => $observation,
                ]
            );
        }

        return redirect()
            ->route('finances.synthese-annuelle-mission.index', ['annee' => $annee])
            ->with('success', __('flash.synthese_transfers_saved'));
    }

    public function impression(Request $request): View
    {
        $this->authorize('viewAny', MissionTresorerieRapportMensuel::class);

        $annee = (int) $request->integer('annee', (int) now()->year);
        $missionId = (int) $request->user()->mission_id;

        return view('finances.synthese-annuelle-mission.impression', [
            'annee' => $annee,
            'nomsMois' => $this->nomsMois(),
            'synthese' => $this->construireSynthese($missionId, $annee),
            'missionNom' => (string) ($request->user()->mission?->nom ?? 'Mission'),
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', MissionTresorerieRapportMensuel::class);

        $annee = (int) $request->integer('annee', (int) now()->year);
        $missionId = (int) $request->user()->mission_id;
        $nomsMois = $this->nomsMois();
        $synthese = $this->construireSynthese($missionId, $annee);
        $missionNom = (string) ($request->user()->mission?->nom ?? 'Mission');

        return Pdf::loadView('finances.synthese-annuelle-mission.impression', compact('annee', 'nomsMois', 'synthese', 'missionNom'))
            ->setPaper('a4', 'landscape')
            ->download('synthese-annuelle-mission-'.$annee.'.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', MissionTresorerieRapportMensuel::class);

        $annee = (int) $request->integer('annee', (int) now()->year);
        $missionId = (int) $request->user()->mission_id;
        $nomsMois = $this->nomsMois();
        $synthese = $this->construireSynthese($missionId, $annee);
        $keysMois = array_keys($nomsMois);

        $rows = [
            array_fill(0, 14, ''),
            ['SECTION: ETAT SYNTHETIQUE DES DIMES ET OFFRANDES', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            $this->ligneExport('Dimes', $synthese['dimes'], $keysMois),
            $this->ligneExport('Offrandes', $synthese['offrandes'], $keysMois),
            $this->ligneExport('Total Di+Off', $synthese['total_dimes_offrandes'], $keysMois),
            $this->ligneExport('% Offr. / Dimes', $synthese['ratio_offrandes_dimes'], $keysMois),
            array_fill(0, 14, ''),
            ['SECTION: REVENUS', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            $this->ligneExport('Revenus dimes (69%)', $synthese['revenus_dimes'], $keysMois),
            $this->ligneExport('Revenus offrandes (20%)', $synthese['revenus_offrandes'], $keysMois),
            $this->ligneExport('Autres offrandes', $synthese['autres_offrandes'], $keysMois),
            $this->ligneExport('Total revenus', $synthese['total_revenus'], $keysMois),
            array_fill(0, 14, ''),
            ['SECTION: ETAT DU TRANSFERT DE FONDS EN DEPOT', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            $this->ligneExport('%tage dimes (31%)', $synthese['transfert_dimes'], $keysMois),
            $this->ligneExport('%tage offrandes (30%)', $synthese['transfert_offrandes'], $keysMois),
            $this->ligneExport('Total a transferer', $synthese['total_a_transferer'], $keysMois),
            $this->ligneExport('Transf. Bank. effectue', $synthese['transfert_banque_effectue'], $keysMois),
            $this->ligneExport('Difference sur transfert', $synthese['difference_transfert'], $keysMois),
        ];

        return Excel::download(
            new SyntheseAnnuelleMissionExport(
                (string) ($request->user()->mission?->nom ?? 'MISSION'),
                $annee,
                $nomsMois,
                $rows
            ),
            'synthese-annuelle-mission-'.$annee.'.xlsx'
        );
    }

    /**
     * @return array{
     *   dimes:array<int,float>,
     *   offrandes:array<int,float>,
     *   total_dimes_offrandes:array<int,float>,
     *   ratio_offrandes_dimes:array<int,float>,
     *   revenus_dimes:array<int,float>,
     *   revenus_offrandes:array<int,float>,
     *   autres_offrandes:array<int,float>,
     *   total_revenus:array<int,float>,
     *   transfert_dimes:array<int,float>,
     *   transfert_offrandes:array<int,float>,
     *   total_a_transferer:array<int,float>,
     *   transfert_banque_effectue:array<int,float>,
     *   difference_transfert:array<int,float>,
     *   observations_transferts:array<int,string>
     * }
     */
    private function construireSynthese(int $missionId, int $annee): array
    {
        $base = $this->tableauMensuelParDefaut();

        $rapports = MissionTresorerieRapportMensuel::query()
            ->where('mission_id', $missionId)
            ->where('annee', $annee)
            ->get(['mois', 'dimes_eglises', 'autres_dimes', 'offrandes_mois']);

        foreach ($rapports as $rapport) {
            $m = (int) $rapport->mois;
            if ($m < 1 || $m > 12) {
                continue;
            }

            $dimes = round((float) $rapport->dimes_eglises + (float) $rapport->autres_dimes, 2);
            $offrandes = round((float) $rapport->offrandes_mois, 2);
            $base['dimes'][$m] = $dimes;
            $base['offrandes'][$m] = $offrandes;
            $base['total_dimes_offrandes'][$m] = round($dimes + $offrandes, 2);
            $base['ratio_offrandes_dimes'][$m] = $dimes > 0 ? round(($offrandes / $dimes) * 100, 2) : 0.0;
        }

        $aggregats = AggregatSyntheseMissionMensuelle::query()
            ->where('mission_id', $missionId)
            ->where('annee', $annee)
            ->get(['mois', 'autres_offrandes']);
        foreach ($aggregats as $agg) {
            $m = (int) $agg->mois;
            if ($m >= 1 && $m <= 12) {
                $base['autres_offrandes'][$m] = round((float) $agg->autres_offrandes, 2);
            }
        }

        if (Schema::hasTable('mission_tresorerie_transferts_bancaires')) {
            $transferts = MissionTresorerieTransfertBancaire::query()
                ->where('mission_id', $missionId)
                ->where('annee', $annee)
                ->get(['mois', 'montant_transfere', 'observation']);
            foreach ($transferts as $transfert) {
                $m = (int) $transfert->mois;
                if ($m >= 1 && $m <= 12) {
                    $base['transfert_banque_effectue'][$m] = round((float) $transfert->montant_transfere, 2);
                    $base['observations_transferts'][$m] = (string) ($transfert->observation ?? '');
                }
            }
        }

        for ($mois = 1; $mois <= 12; $mois++) {
            $base['revenus_dimes'][$mois] = round($base['dimes'][$mois] * 0.69, 2);
            $base['revenus_offrandes'][$mois] = round($base['offrandes'][$mois] * 0.20, 2);
            $base['total_revenus'][$mois] = round(
                $base['revenus_dimes'][$mois] + $base['revenus_offrandes'][$mois] + $base['autres_offrandes'][$mois],
                2
            );

            $base['transfert_dimes'][$mois] = round($base['dimes'][$mois] * 0.31, 2);
            $base['transfert_offrandes'][$mois] = round($base['offrandes'][$mois] * 0.30, 2);
            $base['total_a_transferer'][$mois] = round($base['transfert_dimes'][$mois] + $base['transfert_offrandes'][$mois], 2);
            $base['difference_transfert'][$mois] = round($base['total_a_transferer'][$mois] - $base['transfert_banque_effectue'][$mois], 2);
        }

        return $base;
    }

    /**
     * @return array<int, string>
     */
    private function nomsMois(): array
    {
        $anneeRef = (int) date('Y');
        $out = [];
        for ($m = 1; $m <= 12; $m++) {
            $out[$m] = \Carbon\Carbon::createFromDate($anneeRef, $m, 1)->translatedFormat('M');
        }

        return $out;
    }

    /**
     * @return array{
     *   dimes:array<int,float>,
     *   offrandes:array<int,float>,
     *   total_dimes_offrandes:array<int,float>,
     *   ratio_offrandes_dimes:array<int,float>,
     *   revenus_dimes:array<int,float>,
     *   revenus_offrandes:array<int,float>,
     *   autres_offrandes:array<int,float>,
     *   total_revenus:array<int,float>,
     *   transfert_dimes:array<int,float>,
     *   transfert_offrandes:array<int,float>,
     *   total_a_transferer:array<int,float>,
     *   transfert_banque_effectue:array<int,float>,
     *   difference_transfert:array<int,float>,
     *   observations_transferts:array<int,string>
     * }
     */
    private function tableauMensuelParDefaut(): array
    {
        $zeros = [];
        $texts = [];
        for ($mois = 1; $mois <= 12; $mois++) {
            $zeros[$mois] = 0.0;
            $texts[$mois] = '';
        }

        return [
            'dimes' => $zeros,
            'offrandes' => $zeros,
            'total_dimes_offrandes' => $zeros,
            'ratio_offrandes_dimes' => $zeros,
            'revenus_dimes' => $zeros,
            'revenus_offrandes' => $zeros,
            'autres_offrandes' => $zeros,
            'total_revenus' => $zeros,
            'transfert_dimes' => $zeros,
            'transfert_offrandes' => $zeros,
            'total_a_transferer' => $zeros,
            'transfert_banque_effectue' => $zeros,
            'difference_transfert' => $zeros,
            'observations_transferts' => $texts,
        ];
    }

    private function peutSaisir(Request $request, int $missionId, int $annee): bool
    {
        return $request->user()->can('update', new MissionTresorerieRapportMensuel([
            'mission_id' => $missionId,
            'annee' => $annee,
            'mois' => 1,
        ]));
    }

    /**
     * @param  array<int, float>  $row
     * @param  array<int, int>  $keysMois
     * @return array<int, string|float>
     */
    private function ligneExport(string $label, array $row, array $keysMois): array
    {
        $line = [$label];
        foreach ($keysMois as $m) {
            $line[] = (float) ($row[$m] ?? 0);
        }
        $line[] = (float) array_sum($row);

        return $line;
    }
}
