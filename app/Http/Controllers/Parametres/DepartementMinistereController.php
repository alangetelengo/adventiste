<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\DepartementMinistere;
use App\Models\EgliseLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartementMinistereController extends Controller
{
    public function index(Request $request, EgliseLocale $eglise): View
    {
        $user = $request->user();
        if (
            !$user->hasPermission('parametres.departements.view_any') ||
            (!$user->estUtilisateurMission() && $user->eglise_locale_id !== $eglise->id)
        ) {
            abort(403);
        }

        $departements = $eglise->departementsMinisters()
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('parametres.eglises.departements.index', compact('eglise', 'departements'));
    }

    public function create(Request $request, EgliseLocale $eglise): View
    {
        $user = $request->user();
        if (
            !$user->hasPermission('parametres.departements.create') ||
            (!$user->estUtilisateurMission() && $user->eglise_locale_id !== $eglise->id)
        ) {
            abort(403);
        }

        return view('parametres.eglises.departements.create', compact('eglise'));
    }

    public function store(Request $request, EgliseLocale $eglise): RedirectResponse
    {
        $user = $request->user();
        if (
            !$user->hasPermission('parametres.departements.create') ||
            (!$user->estUtilisateurMission() && $user->eglise_locale_id !== $eglise->id)
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departements_ministeres', 'nom')->where(fn($q) => $q->where('eglise_locale_id', $eglise->id)),
            ],
            'code_unique' => [
                'required',
                'string',
                'max:64',
                Rule::unique('departements_ministeres', 'code_unique')
                    ->where(fn($q) => $q->where('eglise_locale_id', $eglise->id)),
            ],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $departement = $eglise->departementsMinisters()->create([
            'identifiant_public' => (string) Str::uuid(),
            'nom' => $validated['nom'],
            'code_unique' => $validated['code_unique'],
            'actif' => $request->boolean('actif', true),
        ]);

        return redirect()
            ->route('parametres.eglises.departements.edit', [$eglise, $departement])
            ->with('success', 'Département/ministère créé.');
    }

    public function show(Request $request, EgliseLocale $eglise, DepartementMinistere $departement): View
    {
        $user = $request->user();
        if (
            !$user->hasPermission('parametres.departements.view_any') ||
            (!$user->estUtilisateurMission() && $user->eglise_locale_id !== $eglise->id) ||
            $departement->eglise_locale_id !== $eglise->id
        ) {
            abort(403);
        }

        return view('parametres.eglises.departements.show', compact('eglise', 'departement'));
    }

    public function edit(Request $request, EgliseLocale $eglise, DepartementMinistere $departement): View
    {
        $user = $request->user();
        if (
            !$user->hasPermission('parametres.departements.update') ||
            (!$user->estUtilisateurMission() && $user->eglise_locale_id !== $eglise->id) ||
            $departement->eglise_locale_id !== $eglise->id
        ) {
            abort(403);
        }

        return view('parametres.eglises.departements.edit', compact('eglise', 'departement'));
    }

    public function update(Request $request, EgliseLocale $eglise, DepartementMinistere $departement): RedirectResponse
    {
        $user = $request->user();
        if (
            !$user->hasPermission('parametres.departements.update') ||
            (!$user->estUtilisateurMission() && $user->eglise_locale_id !== $eglise->id) ||
            $departement->eglise_locale_id !== $eglise->id
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departements_ministeres', 'nom')
                    ->where(fn($q) => $q->where('eglise_locale_id', $eglise->id))
                    ->ignore($departement->id),
            ],
            'code_unique' => [
                'required',
                'string',
                'max:64',
                Rule::unique('departements_ministeres', 'code_unique')
                    ->ignore($departement->id),
            ],
            'actif' => ['sometimes', 'boolean'],
        ]);

        $departement->update([
            'nom' => $validated['nom'],
            'code_unique' => $validated['code_unique'],
            'actif' => $request->boolean('actif', true),
        ]);

        return redirect()
            ->route('parametres.eglises.departements.edit', [$eglise, $departement])
            ->with('success', 'Département/ministère enregistré.');
    }

    public function destroy(Request $request, EgliseLocale $eglise, DepartementMinistere $departement): RedirectResponse
    {
        $user = $request->user();
        if (
            !$user->hasPermission('parametres.departements.delete') ||
            (!$user->estUtilisateurMission() && $user->eglise_locale_id !== $eglise->id) ||
            $departement->eglise_locale_id !== $eglise->id
        ) {
            abort(403);
        }

        if ($departement->lignesRecap()->exists()) {
            return redirect()
                ->route('parametres.eglises.departements.edit', [$eglise, $departement])
                ->with('error', 'Impossible de supprimer : des collectes sont liées à ce département. Supprimez-les d\'abord.');
        }

        $departement->delete();

        return redirect()
            ->route('parametres.eglises.departements.index', $eglise)
            ->with('success', 'Département/ministère supprimé.');
    }
}
