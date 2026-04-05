<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DistrictController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', District::class);

        $missionId = (int) $request->user()->mission_id;

        $districts = District::query()
            ->withCount('eglisesLocales')
            ->where('mission_id', $missionId)
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('parametres.districts.index', compact('districts'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', District::class);

        return view('parametres.districts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', District::class);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('districts', 'nom')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
        ]);

        $district = District::query()->create([
            'mission_id' => $missionId,
            'nom' => $validated['nom'],
        ]);

        return redirect()
            ->route('parametres.districts.edit', $district)
            ->with('success', __('flash.district_created'));
    }

    public function show(Request $request, District $district): View
    {
        $this->authorize('view', $district);

        $district->loadCount('eglisesLocales');

        return view('parametres.districts.show', compact('district'));
    }

    public function edit(Request $request, District $district): View
    {
        $this->authorize('update', $district);

        return view('parametres.districts.edit', compact('district'));
    }

    public function update(Request $request, District $district): RedirectResponse
    {
        $this->authorize('update', $district);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('districts', 'nom')
                    ->where(fn ($q) => $q->where('mission_id', $missionId))
                    ->ignore($district->id),
            ],
        ]);

        $district->update(['nom' => $validated['nom']]);

        return redirect()
            ->route('parametres.districts.edit', $district)
            ->with('success', __('flash.district_saved'));
    }

    public function destroy(Request $request, District $district): RedirectResponse
    {
        $this->authorize('delete', $district);

        $district->delete();

        return redirect()
            ->route('parametres.districts.index')
            ->with('success', __('flash.district_deleted'));
    }
}
