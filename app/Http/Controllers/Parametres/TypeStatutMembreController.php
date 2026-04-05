<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\TypeStatutMembre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TypeStatutMembreController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TypeStatutMembre::class);

        $missionId = (int) $request->user()->mission_id;
        $types = TypeStatutMembre::query()
            ->where('mission_id', $missionId)
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->get();

        return view('parametres.types-statut-membre.index', compact('types'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TypeStatutMembre::class);

        return view('parametres.types-statut-membre.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TypeStatutMembre::class);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('types_statut_membres', 'code')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
            'libelle' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'couleur' => ['nullable', 'string', 'max:20', 'regex:/^#?[A-Fa-f0-9]{3,8}$/'],
            'ordre' => ['required', 'integer', 'min:0', 'max:65535'],
            'actif' => ['sometimes', 'boolean'],
        ]);

        TypeStatutMembre::query()->create([
            'mission_id' => $missionId,
            'code' => $validated['code'],
            'libelle' => $validated['libelle'],
            'description' => $validated['description'] ?? null,
            'couleur' => $validated['couleur'] ?? null,
            'ordre' => $validated['ordre'],
            'actif' => $request->has('actif') && $request->boolean('actif'),
            'is_system' => false,
        ]);

        return redirect()
            ->route('parametres.types-statut-membre.index')
            ->with('success', __('flash.type_statut_created'));
    }

    public function edit(Request $request, TypeStatutMembre $type): View
    {
        $this->authorize('update', $type);

        return view('parametres.types-statut-membre.edit', ['type' => $type]);
    }

    public function update(Request $request, TypeStatutMembre $type): RedirectResponse
    {
        $this->authorize('update', $type);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('types_statut_membres', 'code')
                    ->where(fn ($q) => $q->where('mission_id', $missionId))
                    ->ignore($type->id),
            ],
            'libelle' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'couleur' => ['nullable', 'string', 'max:20', 'regex:/^#?[A-Fa-f0-9]{3,8}$/'],
            'ordre' => ['required', 'integer', 'min:0', 'max:65535'],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $type->fill([
            'code' => $validated['code'],
            'libelle' => $validated['libelle'],
            'description' => $validated['description'] ?? null,
            'couleur' => $validated['couleur'] ?? null,
            'ordre' => $validated['ordre'],
            'actif' => $request->has('actif') && $request->boolean('actif'),
        ]);
        $type->save();

        return redirect()
            ->route('parametres.types-statut-membre.index')
            ->with('success', __('flash.type_statut_updated'));
    }

    public function destroy(TypeStatutMembre $type): RedirectResponse
    {
        $this->authorize('delete', $type);

        if ($type->is_system) {
            return redirect()
                ->route('parametres.types-statut-membre.index')
                ->with('error', __('flash.type_statut_system_delete'));
        }

        if ($type->membres()->exists() || $type->historiques()->exists()) {
            return redirect()
                ->route('parametres.types-statut-membre.index')
                ->with('error', __('flash.type_statut_in_use'));
        }

        $type->delete();

        return redirect()
            ->route('parametres.types-statut-membre.index')
            ->with('success', __('flash.type_statut_deleted'));
    }
}
