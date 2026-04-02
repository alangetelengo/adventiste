<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\MissionTresorerieVentilationLigne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MissionTresorerieVentilationLignesController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorize('viewAny', MissionTresorerieVentilationLigne::class);

        $missionId = (int) $request->user()->mission_id;
        $lignes = MissionTresorerieVentilationLigne::query()
            ->where('mission_id', $missionId)
            ->orderBy('ordre')
            ->get();

        return view('parametres.tresorerie-ventilation-lignes.edit', compact('lignes'));
    }

    public function update(Request $request): RedirectResponse
    {
        $missionId = (int) $request->user()->mission_id;

        $lignes = MissionTresorerieVentilationLigne::query()
            ->where('mission_id', $missionId)
            ->orderBy('ordre')
            ->get();

        $validated = $request->validate([
            'lignes' => ['required', 'array'],
            'lignes.*.designation' => ['required', 'string', 'max:255'],
            'lignes.*.ordre' => ['required', 'integer', 'min:0', 'max:65535'],
            'lignes.*.pourcentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($lignes as $ligne) {
            $this->authorize('update', $ligne);
            $key = (string) $ligne->id;
            if (! isset($validated['lignes'][$key])) {
                continue;
            }
            $row = $validated['lignes'][$key];
            $ligne->designation = $row['designation'];
            $ligne->ordre = (int) $row['ordre'];
            if (in_array($ligne->kind, [
                MissionTresorerieVentilationLigne::KIND_POURCENTAGE_DIMES,
                MissionTresorerieVentilationLigne::KIND_POURCENTAGE_OFFRANDES,
            ], true)) {
                $p = $row['pourcentage'] ?? null;
                if ($p === null || $p === '') {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['lignes.'.$ligne->id.'.pourcentage' => 'Pourcentage requis pour cette ligne.']);
                }
                $ligne->pourcentage = $p;
            }
            $ligne->save();
        }

        return redirect()
            ->route('parametres.tresorerie-ventilation-lignes.edit')
            ->with('success', 'Lignes de ventilation enregistrées.');
    }
}
