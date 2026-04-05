<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\TypeRecetteMission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TypeRecetteMissionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TypeRecetteMission::class);

        $missionId = (int) $request->user()->mission_id;
        $types = TypeRecetteMission::query()
            ->where('mission_id', $missionId)
            ->orderBy('ordre')
            ->get();

        return view('parametres.types-recette.index', compact('types'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TypeRecetteMission::class);

        return view('parametres.types-recette.create', [
            'categories' => TypeRecetteMission::libellesCategories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TypeRecetteMission::class);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('types_recette_mission', 'code')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
            'libelle' => ['required', 'string', 'max:255'],
            'categorie' => ['required', 'string', Rule::in([
                TypeRecetteMission::CATEGORIE_DIME,
                TypeRecetteMission::CATEGORIE_OFFRANDE,
                TypeRecetteMission::CATEGORIE_DON,
            ])],
            'ordre' => ['required', 'integer', 'min:0', 'max:65535'],
            'actif' => ['sometimes', 'boolean'],
            'exclure_rapport_mission' => ['sometimes', 'boolean'],
            'mission_sans_partage' => ['sometimes', 'boolean'],
        ]);

        TypeRecetteMission::query()->create([
            'mission_id' => $missionId,
            'code' => $validated['code'],
            'libelle' => $validated['libelle'],
            'categorie' => $validated['categorie'],
            'ordre' => $validated['ordre'],
            'actif' => $request->has('actif') && $request->boolean('actif'),
            'exclure_rapport_mission' => $request->has('exclure_rapport_mission') && $request->boolean('exclure_rapport_mission'),
            'mission_sans_partage' => $request->has('mission_sans_partage') && $request->boolean('mission_sans_partage'),
        ]);

        return redirect()
            ->route('parametres.types-recette.index')
            ->with('success', __('flash.type_recette_created'));
    }

    public function edit(Request $request, TypeRecetteMission $type): View
    {
        $this->authorize('update', $type);

        return view('parametres.types-recette.edit', [
            'type' => $type,
            'categories' => TypeRecetteMission::libellesCategories(),
        ]);
    }

    public function update(Request $request, TypeRecetteMission $type): RedirectResponse
    {
        $this->authorize('update', $type);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('types_recette_mission', 'code')
                    ->where(fn ($q) => $q->where('mission_id', $missionId))
                    ->ignore($type->id),
            ],
            'libelle' => ['required', 'string', 'max:255'],
            'categorie' => ['required', 'string', Rule::in([
                TypeRecetteMission::CATEGORIE_DIME,
                TypeRecetteMission::CATEGORIE_OFFRANDE,
                TypeRecetteMission::CATEGORIE_DON,
            ])],
            'ordre' => ['required', 'integer', 'min:0', 'max:65535'],
            'actif' => ['sometimes', 'boolean'],
            'exclure_rapport_mission' => ['sometimes', 'boolean'],
            'mission_sans_partage' => ['sometimes', 'boolean'],
        ]);

        $type->fill([
            'code' => $validated['code'],
            'libelle' => $validated['libelle'],
            'categorie' => $validated['categorie'],
            'ordre' => $validated['ordre'],
            'actif' => $request->has('actif') && $request->boolean('actif'),
            'exclure_rapport_mission' => $request->has('exclure_rapport_mission') && $request->boolean('exclure_rapport_mission'),
            'mission_sans_partage' => $request->has('mission_sans_partage') && $request->boolean('mission_sans_partage'),
        ]);
        $type->save();

        return redirect()
            ->route('parametres.types-recette.index')
            ->with('success', __('flash.type_recette_updated'));
    }

    public function destroy(TypeRecetteMission $type): RedirectResponse
    {
        $this->authorize('delete', $type);

        if ($type->lignesRecap()->exists()) {
            return redirect()
                ->route('parametres.types-recette.index')
                ->with('error', __('flash.type_recette_in_use'));
        }

        $type->delete();

        return redirect()
            ->route('parametres.types-recette.index')
            ->with('success', __('flash.type_recette_deleted'));
    }
}
