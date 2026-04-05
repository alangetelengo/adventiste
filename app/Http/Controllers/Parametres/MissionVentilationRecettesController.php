<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\MissionReglesVentilationRecettes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MissionVentilationRecettesController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorize('viewAny', MissionReglesVentilationRecettes::class);

        $missionId = (int) $request->user()->mission_id;
        $regles = MissionReglesVentilationRecettes::query()->firstOrNew(
            ['mission_id' => $missionId],
            [
                'part_mission_dime_pct' => 100,
                'part_mission_offrande_pct' => 50,
                'part_mission_don_pct' => 0,
            ]
        );

        return view('parametres.ventilation-recettes.edit', compact('regles'));
    }

    public function update(Request $request): RedirectResponse
    {
        $missionId = (int) $request->user()->mission_id;

        $regles = MissionReglesVentilationRecettes::query()->firstOrNew(
            ['mission_id' => $missionId],
            [
                'part_mission_dime_pct' => 100,
                'part_mission_offrande_pct' => 50,
                'part_mission_don_pct' => 0,
            ]
        );
        $regles->mission_id = $missionId;

        $this->authorize('update', $regles);

        $validated = $request->validate([
            'part_mission_dime_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'part_mission_offrande_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'part_mission_don_pct' => ['required', 'numeric', 'min:0', 'max:100'],
            'libelle_rapport_dime' => ['nullable', 'string', 'max:120'],
            'libelle_rapport_offrande' => ['nullable', 'string', 'max:120'],
            'libelle_rapport_don' => ['nullable', 'string', 'max:120'],
            'notes_internes' => ['nullable', 'string', 'max:5000'],
        ]);

        $regles->fill($validated);
        $regles->save();

        return redirect()
            ->route('parametres.ventilation-recettes.edit')
            ->with('success', __('flash.ventilation_recettes_saved'));
    }
}
