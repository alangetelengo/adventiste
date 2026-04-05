<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\EntreeFinanciereGroupeMission;
use App\Models\GroupeMission;
use App\Support\MontantFcfa;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EntreeFinanciereGroupeMissionController extends Controller
{
    public function index(Request $request, GroupeMission $groupe): View
    {
        $this->authorize('view', $groupe);

        $missionId = (int) $request->user()->mission_id;
        if ($groupe->mission_id !== $missionId) {
            abort(403);
        }

        $entrees = $groupe->entreesFinancieres()
            ->orderByDesc('annee')
            ->orderByDesc('mois')
            ->paginate(20)
            ->withQueryString();

        return view('parametres.groupes-mission.entrees.index', compact('groupe', 'entrees'));
    }

    public function create(Request $request, GroupeMission $groupe): View
    {
        $this->authorize('update', $groupe);

        $missionId = (int) $request->user()->mission_id;
        if ($groupe->mission_id !== $missionId) {
            abort(403);
        }

        $now = CarbonImmutable::now();
        $annee = (int) $request->query('annee', $now->year);
        $mois = (int) $request->query('mois', $now->month);

        return view('parametres.groupes-mission.entrees.create', compact('groupe', 'annee', 'mois'));
    }

    public function store(Request $request, GroupeMission $groupe): RedirectResponse
    {
        $this->authorize('update', $groupe);

        $missionId = (int) $request->user()->mission_id;
        if ($groupe->mission_id !== $missionId) {
            abort(403);
        }

        $validated = $request->validate([
            'annee' => ['required', 'integer', 'min:2000', 'max:2100'],
            'mois' => ['required', 'integer', 'min:1', 'max:12'],
            'dimes' => ['nullable', 'numeric', 'min:0.00'],
            'offrande_ecole_sabbat' => ['nullable', 'numeric', 'min:0.00'],
            'offrande_budget_eglise' => ['nullable', 'numeric', 'min:0.00'],
            'offrande_fonds_mission' => ['nullable', 'numeric', 'min:0.00'],
            'offrande_autres' => ['nullable', 'numeric', 'min:0.00'],
        ]);

        $entree = EntreeFinanciereGroupeMission::query()->firstOrCreate(
            [
                'groupe_mission_id' => $groupe->id,
                'annee' => $validated['annee'],
                'mois' => $validated['mois'],
            ],
            [
                'dimes' => MontantFcfa::parseToNumericString($validated['dimes']) ?? 0,
                'offrande_ecole_sabbat' => MontantFcfa::parseToNumericString($validated['offrande_ecole_sabbat']) ?? 0,
                'offrande_budget_eglise' => MontantFcfa::parseToNumericString($validated['offrande_budget_eglise']) ?? 0,
                'offrande_fonds_mission' => MontantFcfa::parseToNumericString($validated['offrande_fonds_mission']) ?? 0,
                'offrande_autres' => MontantFcfa::parseToNumericString($validated['offrande_autres']) ?? 0,
            ]
        );

        if ($request->has('update')) {
            $entree->update([
                'dimes' => MontantFcfa::parseToNumericString($validated['dimes']) ?? 0,
                'offrande_ecole_sabbat' => MontantFcfa::parseToNumericString($validated['offrande_ecole_sabbat']) ?? 0,
                'offrande_budget_eglise' => MontantFcfa::parseToNumericString($validated['offrande_budget_eglise']) ?? 0,
                'offrande_fonds_mission' => MontantFcfa::parseToNumericString($validated['offrande_fonds_mission']) ?? 0,
                'offrande_autres' => MontantFcfa::parseToNumericString($validated['offrande_autres']) ?? 0,
            ]);
        }

        return redirect()
            ->route('parametres.groupes-mission.entrees.index', $groupe)
            ->with('success', __('flash.entree_financiere_saved'));
    }

    public function edit(Request $request, GroupeMission $groupe, EntreeFinanciereGroupeMission $entree): View
    {
        $this->authorize('update', $groupe);

        $missionId = (int) $request->user()->mission_id;
        if ($groupe->mission_id !== $missionId || $entree->groupe_mission_id !== $groupe->id) {
            abort(403);
        }

        return view('parametres.groupes-mission.entrees.edit', compact('groupe', 'entree'));
    }

    public function update(Request $request, GroupeMission $groupe, EntreeFinanciereGroupeMission $entree): RedirectResponse
    {
        $this->authorize('update', $groupe);

        $missionId = (int) $request->user()->mission_id;
        if ($groupe->mission_id !== $missionId || $entree->groupe_mission_id !== $groupe->id) {
            abort(403);
        }

        $validated = $request->validate([
            'dimes' => ['nullable', 'numeric', 'min:0.00'],
            'offrande_ecole_sabbat' => ['nullable', 'numeric', 'min:0.00'],
            'offrande_budget_eglise' => ['nullable', 'numeric', 'min:0.00'],
            'offrande_fonds_mission' => ['nullable', 'numeric', 'min:0.00'],
            'offrande_autres' => ['nullable', 'numeric', 'min:0.00'],
        ]);

        $entree->update([
            'dimes' => MontantFcfa::parseToNumericString($validated['dimes']) ?? $entree->dimes,
            'offrande_ecole_sabbat' => MontantFcfa::parseToNumericString($validated['offrande_ecole_sabbat']) ?? $entree->offrande_ecole_sabbat,
            'offrande_budget_eglise' => MontantFcfa::parseToNumericString($validated['offrande_budget_eglise']) ?? $entree->offrande_budget_eglise,
            'offrande_fonds_mission' => MontantFcfa::parseToNumericString($validated['offrande_fonds_mission']) ?? $entree->offrande_fonds_mission,
            'offrande_autres' => MontantFcfa::parseToNumericString($validated['offrande_autres']) ?? $entree->offrande_autres,
        ]);

        return redirect()
            ->route('parametres.groupes-mission.entrees.index', $groupe)
            ->with('success', __('flash.entree_financiere_saved'));
    }

    public function destroy(Request $request, GroupeMission $groupe, EntreeFinanciereGroupeMission $entree): RedirectResponse
    {
        $this->authorize('update', $groupe);

        $missionId = (int) $request->user()->mission_id;
        if ($groupe->mission_id !== $missionId || $entree->groupe_mission_id !== $groupe->id) {
            abort(403);
        }

        $entree->delete();

        return redirect()
            ->route('parametres.groupes-mission.entrees.index', $groupe)
            ->with('success', __('flash.entree_financiere_deleted'));
    }
}
