<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\EgliseLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EgliseLocaleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', EgliseLocale::class);

        $missionId = (int) $request->user()->mission_id;

        $eglises = EgliseLocale::query()
            ->with(['district', 'mission'])
            ->where('mission_id', $missionId)
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        return view('parametres.eglises.index', compact('eglises'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', EgliseLocale::class);

        $districts = District::query()
            ->where('mission_id', $request->user()->mission_id)
            ->orderBy('nom')
            ->get();

        return view('parametres.eglises.create', compact('districts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', EgliseLocale::class);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'code_unique' => ['required', 'string', 'max:64', 'unique:eglises_locales,code_unique'],
            'actif' => ['sometimes', 'boolean'],
            'district_id' => [
                'nullable',
                Rule::exists('districts', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
            'indicateurs_json' => ['nullable', 'string'],
        ]);

        $indicateurs = $this->decodeIndicateursJson($request->input('indicateurs_json'));
        if ($indicateurs === false) {
            return back()->withInput()->withErrors(['indicateurs_json' => 'JSON invalide pour les indicateurs financiers.']);
        }

        $eglise = EgliseLocale::query()->create([
            'mission_id' => $missionId,
            'district_id' => $validated['district_id'] ?? null,
            'nom' => $validated['nom'],
            'code_unique' => $validated['code_unique'],
            'actif' => $request->boolean('actif', true),
            'indicateurs_financiers' => $indicateurs,
        ]);

        return redirect()
            ->route('parametres.eglises.edit', $eglise)
            ->with('success', __('flash.eglise_created'));
    }

    public function show(Request $request, EgliseLocale $eglise): View
    {
        $this->authorize('view', $eglise);

        $eglise->load(['district', 'mission']);

        return view('parametres.eglises.show', compact('eglise'));
    }

    public function edit(Request $request, EgliseLocale $eglise): View
    {
        $this->authorize('update', $eglise);

        $districts = District::query()
            ->where('mission_id', $request->user()->mission_id)
            ->orderBy('nom')
            ->get();

        return view('parametres.eglises.edit', compact('eglise', 'districts'));
    }

    public function update(Request $request, EgliseLocale $eglise): RedirectResponse
    {
        $this->authorize('update', $eglise);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'code_unique' => [
                'required',
                'string',
                'max:64',
                Rule::unique('eglises_locales', 'code_unique')->ignore($eglise->id),
            ],
            'actif' => ['sometimes', 'boolean'],
            'district_id' => [
                'nullable',
                Rule::exists('districts', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
            'indicateurs_json' => ['nullable', 'string'],
        ]);

        $indicateurs = $this->decodeIndicateursJson($request->input('indicateurs_json'));
        if ($indicateurs === false) {
            return back()->withInput()->withErrors(['indicateurs_json' => 'JSON invalide pour les indicateurs financiers.']);
        }

        $eglise->update([
            'district_id' => $validated['district_id'] ?? null,
            'nom' => $validated['nom'],
            'code_unique' => $validated['code_unique'],
            'actif' => $request->boolean('actif', true),
            'indicateurs_financiers' => $indicateurs,
        ]);

        return redirect()
            ->route('parametres.eglises.edit', $eglise)
            ->with('success', __('flash.eglise_saved'));
    }

    public function destroy(Request $request, EgliseLocale $eglise): RedirectResponse
    {
        $this->authorize('delete', $eglise);

        $eglise->delete();

        return redirect()
            ->route('parametres.eglises.index')
            ->with('success', __('flash.eglise_deleted'));
    }

    /**
     * @return array<string, mixed>|null|false null = vider ; false = JSON invalide
     */
    private function decodeIndicateursJson(?string $raw): array|null|false
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return false;
        }

        return $decoded;
    }
}
