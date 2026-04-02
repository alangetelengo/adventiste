<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\GroupeMission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupeMissionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GroupeMission::class);

        $missionId = (int) $request->user()->mission_id;

        $groupes = GroupeMission::query()
            ->withCount(['membres', 'entreesFinancieres'])
            ->where('mission_id', $missionId)
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('parametres.groupes-mission.index', compact('groupes'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', GroupeMission::class);

        return view('parametres.groupes-mission.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GroupeMission::class);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groupes_mission', 'nom')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
            'code_unique' => ['required', 'string', 'max:64', 'unique:groupes_mission,code_unique'],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $groupe = GroupeMission::query()->create([
            'mission_id' => $missionId,
            'nom' => $validated['nom'],
            'code_unique' => $validated['code_unique'],
            'actif' => $request->boolean('actif', true),
        ]);

        return redirect()
            ->route('parametres.groupes-mission.edit', $groupe)
            ->with('success', 'Groupe mission créé.');
    }

    public function show(Request $request, GroupeMission $groupe): View
    {
        $this->authorize('view', $groupe);

        $groupe->loadCount(['membres', 'entreesFinancieres']);

        return view('parametres.groupes-mission.show', compact('groupe'));
    }

    public function edit(Request $request, GroupeMission $groupe): View
    {
        $this->authorize('update', $groupe);

        return view('parametres.groupes-mission.edit', compact('groupe'));
    }

    public function update(Request $request, GroupeMission $groupe): RedirectResponse
    {
        $this->authorize('update', $groupe);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groupes_mission', 'nom')
                    ->where(fn ($q) => $q->where('mission_id', $missionId))
                    ->ignore($groupe->id),
            ],
            'code_unique' => [
                'required',
                'string',
                'max:64',
                Rule::unique('groupes_mission', 'code_unique')->ignore($groupe->id),
            ],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $groupe->update([
            'nom' => $validated['nom'],
            'code_unique' => $validated['code_unique'],
            'actif' => $request->boolean('actif', true),
        ]);

        return redirect()
            ->route('parametres.groupes-mission.edit', $groupe)
            ->with('success', 'Groupe mission enregistré.');
    }

    public function destroy(Request $request, GroupeMission $groupe): RedirectResponse
    {
        $this->authorize('delete', $groupe);

        if ($groupe->membres()->exists()) {
            return redirect()
                ->route('parametres.groupes-mission.edit', $groupe)
                ->with('error', 'Impossible de supprimer : des membres sont encore rattachés à ce groupe. Retirez le groupe sur les fiches membres d’abord.');
        }

        if ($groupe->entreesFinancieres()->exists()) {
            return redirect()
                ->route('parametres.groupes-mission.edit', $groupe)
                ->with('error', 'Impossible de supprimer : des lignes de finances mission existent pour ce groupe.');
        }

        $groupe->delete();

        return redirect()
            ->route('parametres.groupes-mission.index')
            ->with('success', 'Groupe mission supprimé.');
    }
}
