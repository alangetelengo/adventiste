<?php

namespace App\Http\Controllers\Finances;

use App\Http\Controllers\Controller;
use App\Models\RapportStationMission;
use App\Models\LigneRapportStationMission;
use App\Models\LigneAutresDime;
use App\Models\MissionTresorerieVentilationLigne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RapportStationMissionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RapportStationMission::class);

        $missionId = (int) $request->user()->mission_id;

        $rapports = RapportStationMission::query()
            ->where('mission_id', $missionId)
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->paginate(24)
            ->withQueryString();

        return view('finances.rapports-station.index', compact('rapports'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', RapportStationMission::class);

        return view('finances.rapports-station.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', RapportStationMission::class);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'annee' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mois' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $rapport = RapportStationMission::query()->firstOrCreate(
            [
                'mission_id' => $missionId,
                'annee' => $validated['annee'],
                'mois' => $validated['mois'],
            ]
        );

        $this->creerLignesParDefaut($rapport);

        return redirect()
            ->route('finances.rapports-station.edit', $rapport)
            ->with('success', __('flash.rapport_station_created'));
    }

    public function show(Request $request, RapportStationMission $rapport): View
    {
        $this->authorize('view', $rapport);

        $rapport->loadMissing('mission', 'lignes', 'lignesAutresDimes');

        return view('finances.rapports-station.show', compact('rapport'));
    }

    public function edit(Request $request, RapportStationMission $rapport): View
    {
        $this->authorize('update', $rapport);

        $rapport->loadMissing('mission', 'lignes', 'lignesAutresDimes');

        return view('finances.rapports-station.edit', compact('rapport'));
    }

    public function update(Request $request, RapportStationMission $rapport): RedirectResponse
    {
        $this->authorize('update', $rapport);

        $validated = $request->validate([
            'lignes' => ['required', 'array'],
            'lignes.*.montant_mois' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_mois_saisi_manuel' => ['sometimes', 'boolean'],
            'autres_dimes' => ['sometimes', 'array'],
            'autres_dimes.*.montant_mois' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Mettre à jour les lignes
        if (isset($validated['lignes'])) {
            foreach ($validated['lignes'] as $ligneId => $data) {
                $ligne = LigneRapportStationMission::query()->find($ligneId);
                if ($ligne && $ligne->rapport_station_mission_id === $rapport->id) {
                    $ligne->update([
                        'montant_mois' => $data['montant_mois'] ?? 0,
                        'montant_mois_saisi_manuel' => (bool) ($data['montant_mois_saisi_manuel'] ?? false),
                    ]);
                }
            }
        }

        // Mettre à jour les autres dîmes
        if (isset($validated['autres_dimes'])) {
            foreach ($validated['autres_dimes'] as $autreId => $data) {
                $autre = LigneAutresDime::query()->find($autreId);
                if ($autre && $autre->rapport_station_mission_id === $rapport->id) {
                    $autre->update([
                        'montant_mois' => $data['montant_mois'] ?? 0,
                    ]);
                }
            }
        }

        $rapport->update([
            'dernier_remplissage_auto_le' => now(),
        ]);

        return redirect()
            ->route('finances.rapports-station.show', $rapport)
            ->with('success', __('flash.rapport_station_saved'));
    }

    public function destroy(Request $request, RapportStationMission $rapport): RedirectResponse
    {
        $this->authorize('delete', $rapport);

        $rapport->delete();

        return redirect()
            ->route('finances.rapports-station.index')
            ->with('success', __('flash.rapport_station_deleted'));
    }

    private function creerLignesParDefaut(RapportStationMission $rapport): void
    {
        if ($rapport->lignes()->exists()) {
            return;
        }

        $lignesVentilation = MissionTresorerieVentilationLigne::query()
            ->where('mission_id', $rapport->mission_id)
            ->orderBy('ordre')
            ->get();

        $ordreTri = 0;
        foreach ($lignesVentilation as $ligne) {
            if ($ligne->estTitre()) {
                continue;
            }
            $code = $ligne->code;
            if ($code === null || $code === '') {
                $code = 'ligne_'.$ligne->id;
            }
            LigneRapportStationMission::create([
                'rapport_station_mission_id' => $rapport->id,
                'code_ligne' => $code,
                'pourcentage' => $ligne->pourcentage,
                'montant_mois' => 0,
                'montant_periode_precedente' => 0,
                'montant_cumule' => 0,
                'montant_mois_saisi_manuel' => false,
                'ordre_tri' => $ordreTri++,
            ]);
        }
    }
}
