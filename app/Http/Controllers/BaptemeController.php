<?php

namespace App\Http\Controllers;

use App\Models\Bapteme;
use App\Models\EgliseLocale;
use App\Models\Membre;
use App\Models\MembreHistoriqueStatut;
use App\Models\TypeStatutMembre;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BaptemeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Bapteme::class);

        $user = $request->user();
        $missionId = (int) $user->mission_id;

        $query = Bapteme::query()
            ->with(['egliseLocale', 'membre'])
            ->orderByDesc('date_bapteme')
            ->orderBy('nom')
            ->orderBy('prenom');

        if ($user->eglise_locale_id !== null) {
            $query->where('eglise_locale_id', $user->eglise_locale_id);
        } else {
            $query->whereHas('egliseLocale', fn ($q) => $q->where('mission_id', $missionId));
        }

        if ($request->filled('eglise_locale_id') && $user->eglise_locale_id === null) {
            $egliseId = (int) $request->input('eglise_locale_id');
            $ok = EgliseLocale::query()
                ->where('mission_id', $missionId)
                ->whereKey($egliseId)
                ->exists();
            if ($ok) {
                $query->where('eglise_locale_id', $egliseId);
            }
        }

        if ($request->filled('q')) {
            $q = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->string('q')->trim()).'%';
            $query->where(function ($sub) use ($q) {
                $sub->where('nom', 'like', $q)
                    ->orWhere('prenom', 'like', $q)
                    ->orWhere('officiant', 'like', $q);
            });
        }

        if ($request->filled('type_bapteme')) {
            $query->where('type_bapteme', $request->string('type_bapteme'));
        }

        $baptemes = $query->paginate(20)->withQueryString();

        $eglisesFiltre = null;
        if ($user->eglise_locale_id === null) {
            $eglisesFiltre = EgliseLocale::query()
                ->where('mission_id', $missionId)
                ->orderBy('nom')
                ->get();
        }

        $typesBapteme = Bapteme::labelsTypes();

        return view('baptemes.index', compact('baptemes', 'eglisesFiltre', 'typesBapteme'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Bapteme::class);

        [$eglises, $egliseParDefaut] = $this->optionsFormulaire($request);
        $typesBapteme = Bapteme::labelsTypes();

        return view('baptemes.create', compact('eglises', 'egliseParDefaut', 'typesBapteme'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Bapteme::class);

        $validated = $this->validatePayload($request);

        $user = $request->user();
        if ($user->eglise_locale_id !== null
            && (int) $validated['eglise_locale_id'] !== (int) $user->eglise_locale_id) {
            abort(403);
        }

        $payload = $this->normalizePayload($validated);

        $dateAdmission = $validated['date_admission_eglise'] ?? null;
        if ($dateAdmission === null || $dateAdmission === '') {
            $dateAdmission = $payload['date_bapteme'];
        }

        unset($payload['date_admission_eglise']);

        $missionId = (int) EgliseLocale::query()->whereKey((int) $payload['eglise_locale_id'])->value('mission_id');
        [$typeStatutId, $membreActif] = $this->typeStatutPourNouveauBaptise($missionId);

        $membre = Membre::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => (int) $payload['eglise_locale_id'],
            'groupe_mission_id' => null,
            'nom' => $payload['nom'],
            'prenom' => $payload['prenom'],
            'mode_entree' => Membre::MODE_ENTREE_BAPTEME,
            'type_bapteme_entree' => $payload['type_bapteme'],
            'date_bapteme' => $payload['date_bapteme'],
            'date_admission_eglise' => $dateAdmission,
            'lieu_bapteme' => $payload['lieu_bapteme'] ?? null,
            'baptise_par' => $payload['officiant'] ?? null,
            'type_statut_membre_id' => $typeStatutId,
            'actif' => $membreActif,
        ]);

        if ($typeStatutId !== null) {
            $codeApplique = TypeStatutMembre::query()->whereKey($typeStatutId)->value('code');
            $motif = $codeApplique === TypeStatutMembre::CODE_REGULIER
                ? 'Statut initial : Régulier (nouveau baptisé).'
                : 'Statut initial lors de l\'enregistrement du baptême.';

            MembreHistoriqueStatut::query()->create([
                'membre_id' => $membre->id,
                'type_statut_membre_id' => $typeStatutId,
                'change_par_user_id' => $request->user()->id,
                'motif' => $motif,
                'changed_at' => now(),
            ]);
        }

        $bapteme = Bapteme::query()->create(array_merge($payload, [
            'identifiant_public' => (string) Str::uuid(),
            'membre_id' => $membre->id,
        ]));

        return redirect()
            ->route('baptemes.show', $bapteme)
            ->with('success', 'Baptême enregistré.');
    }

    public function show(Bapteme $bapteme): View
    {
        $this->authorize('view', $bapteme);

        $bapteme->load(['egliseLocale', 'membre']);

        return view('baptemes.show', compact('bapteme'));
    }

    public function edit(Request $request, Bapteme $bapteme): View
    {
        $this->authorize('update', $bapteme);

        [$eglises, $egliseParDefaut] = $this->optionsFormulaire($request, (int) $bapteme->eglise_locale_id);
        $typesBapteme = Bapteme::labelsTypes();

        return view('baptemes.edit', compact('bapteme', 'eglises', 'egliseParDefaut', 'typesBapteme'));
    }

    public function update(Request $request, Bapteme $bapteme): RedirectResponse
    {
        $this->authorize('update', $bapteme);

        $validated = $this->validatePayload($request);

        $user = $request->user();
        if ($user->eglise_locale_id !== null
            && (int) $validated['eglise_locale_id'] !== (int) $user->eglise_locale_id) {
            abort(403);
        }

        $payload = $this->normalizePayload($validated);

        $dateAdmission = $validated['date_admission_eglise'] ?? null;
        if ($dateAdmission === null || $dateAdmission === '') {
            $dateAdmission = $payload['date_bapteme'];
        }

        unset($payload['date_admission_eglise']);

        $bapteme->update($payload);

        $membre = $bapteme->membre;
        if ($membre !== null) {
            $membre->update([
                'eglise_locale_id' => (int) $payload['eglise_locale_id'],
                'nom' => $payload['nom'],
                'prenom' => $payload['prenom'],
                'mode_entree' => Membre::MODE_ENTREE_BAPTEME,
                'type_bapteme_entree' => $payload['type_bapteme'],
                'date_bapteme' => $payload['date_bapteme'],
                'date_admission_eglise' => $dateAdmission,
                'lieu_bapteme' => $payload['lieu_bapteme'] ?? null,
                'baptise_par' => $payload['officiant'] ?? null,
                'recu_dans_eglise_de' => null,
                'recu_le' => null,
            ]);
        }

        return redirect()
            ->route('baptemes.show', $bapteme)
            ->with('success', 'Baptême mis à jour.');
    }

    public function destroy(Bapteme $bapteme): RedirectResponse
    {
        $this->authorize('delete', $bapteme);

        $bapteme->delete();

        return redirect()
            ->route('baptemes.index')
            ->with('success', 'Baptême supprimé.');
    }

    public function certificat(Bapteme $bapteme): View
    {
        $this->authorize('certificat', $bapteme);

        $bapteme->load(['egliseLocale.district', 'membre']);
        $typesBapteme = Bapteme::labelsTypes();

        return view('baptemes.certificat', compact('bapteme', 'typesBapteme'));
    }

    /**
     * @return array{0: Collection<int, EgliseLocale>, 1?: int|null}
     */
    private function optionsFormulaire(Request $request, ?int $egliseChoisieId = null): array
    {
        $user = $request->user();
        $missionId = (int) $user->mission_id;

        $eglises = EgliseLocale::query()
            ->where('mission_id', $missionId)
            ->when($user->eglise_locale_id !== null, fn ($q) => $q->whereKey($user->eglise_locale_id))
            ->orderBy('nom')
            ->get();

        $egliseParDefaut = $egliseChoisieId ?? $user->eglise_locale_id;

        return [$eglises, $egliseParDefaut];
    }

    /** @return array<string, mixed> */
    private function validatePayload(Request $request): array
    {
        $user = $request->user();
        $missionId = (int) $user->mission_id;
        $egliseRule = Rule::exists('eglises_locales', 'id')->where(fn ($q) => $q->where('mission_id', $missionId));

        return $request->validate([
            'eglise_locale_id' => ['required', 'integer', $egliseRule],
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'type_bapteme' => ['required', Rule::in(array_keys(Bapteme::labelsTypes()))],
            'date_bapteme' => ['required', 'date'],
            'date_admission_eglise' => ['nullable', 'date'],
            'lieu_bapteme' => ['nullable', 'string', 'max:255'],
            'officiant' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function normalizePayload(array $payload): array
    {
        $payload['nom'] = $this->toUpper($payload['nom'] ?? null);
        $payload['prenom'] = $this->toInitialCaps($payload['prenom'] ?? null);
        $payload['officiant'] = $this->toUpper($payload['officiant'] ?? null);
        $payload['lieu_bapteme'] = $this->toUpper($payload['lieu_bapteme'] ?? null);
        $payload['notes'] = $this->toInitialCaps($payload['notes'] ?? null);

        return $payload;
    }

    private function toUpper(mixed $value): ?string
    {
        $str = trim((string) ($value ?? ''));

        return $str === '' ? null : mb_strtoupper($str, 'UTF-8');
    }

    private function toInitialCaps(mixed $value): ?string
    {
        $str = trim((string) ($value ?? ''));
        if ($str === '') {
            return null;
        }

        $lower = mb_strtolower($str, 'UTF-8');

        return mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @return array{0: ?int, 1: bool} Identifiant du type « Régulier » si présent pour la mission, sinon « Actif », et indicateur membre.actif.
     */
    private function typeStatutPourNouveauBaptise(int $missionId): array
    {
        if ($missionId <= 0) {
            return [null, true];
        }

        $regulier = TypeStatutMembre::query()
            ->where('mission_id', $missionId)
            ->where('code', TypeStatutMembre::CODE_REGULIER)
            ->where('actif', true)
            ->first();
        if ($regulier !== null) {
            return [(int) $regulier->id, $regulier->code !== TypeStatutMembre::CODE_REFROIDI];
        }

        $actif = TypeStatutMembre::query()
            ->where('mission_id', $missionId)
            ->where('code', TypeStatutMembre::CODE_ACTIF)
            ->where('actif', true)
            ->first();

        return $actif !== null
            ? [(int) $actif->id, $actif->code !== TypeStatutMembre::CODE_REFROIDI]
            : [null, true];
    }
}
