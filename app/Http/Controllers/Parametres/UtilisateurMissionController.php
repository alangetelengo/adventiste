<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\EgliseLocale;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UtilisateurMissionController extends Controller
{
    /** @return list<string> */
    private static function roleNamesRequiringEglise(): array
    {
        return ['tresorier_eglise', 'secretaire_eglise'];
    }

    private function roleRequiresEglise(Role $role): bool
    {
        return in_array($role->name, self::roleNamesRequiringEglise(), true);
    }

    /** @return list<int> */
    private function roleIdsRequiringEglise(): array
    {
        return Role::query()
            ->whereIn('name', self::roleNamesRequiringEglise())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $missionId = (int) $request->user()->mission_id;

        $utilisateurs = User::query()
            ->with(['egliseLocale', 'role'])
            ->where('mission_id', $missionId)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('parametres.utilisateurs.index', compact('utilisateurs'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', User::class);

        $eglises = EgliseLocale::query()
            ->where('mission_id', $request->user()->mission_id)
            ->orderBy('nom')
            ->get();

        $roles = Role::query()->orderBy('label')->get();
        $roleIdsRequiringEglise = $this->roleIdsRequiringEglise();

        return view('parametres.utilisateurs.create', compact('eglises', 'roles', 'roleIdsRequiringEglise'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $missionId = (int) $request->user()->mission_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'eglise_locale_id' => [
                Rule::requiredIf(function () use ($request): bool {
                    $role = Role::query()->find((int) $request->input('role_id'));

                    return $role !== null && $this->roleRequiresEglise($role);
                }),
                'nullable',
                Rule::exists('eglises_locales', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
        ]);

        $role = Role::query()->findOrFail($validated['role_id']);
        $egliseId = $this->roleRequiresEglise($role)
            ? (int) $validated['eglise_locale_id']
            : null;

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'mission_id' => $missionId,
            'eglise_locale_id' => $egliseId,
            'role_id' => $role->id,
            'identifiant_public' => (string) Str::uuid(),
        ]);

        return redirect()
            ->route('parametres.utilisateurs.edit', $user)
            ->with('success', __('flash.utilisateur_created'));
    }

    public function edit(Request $request, User $utilisateur): View
    {
        $this->authorize('update', $utilisateur);

        $eglises = EgliseLocale::query()
            ->where('mission_id', $request->user()->mission_id)
            ->orderBy('nom')
            ->get();

        $roles = Role::query()->orderBy('label')->get();
        $roleIdsRequiringEglise = $this->roleIdsRequiringEglise();

        return view('parametres.utilisateurs.edit', compact('utilisateur', 'eglises', 'roles', 'roleIdsRequiringEglise'));
    }

    public function update(Request $request, User $utilisateur): RedirectResponse
    {
        $this->authorize('update', $utilisateur);

        $missionId = (int) $request->user()->mission_id;
        $adminRoleId = Role::idFor('admin_mission');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($utilisateur->id),
            ],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'eglise_locale_id' => [
                Rule::requiredIf(function () use ($request): bool {
                    $role = Role::query()->find((int) $request->input('role_id'));

                    return $role !== null && $this->roleRequiresEglise($role);
                }),
                'nullable',
                Rule::exists('eglises_locales', 'id')->where(fn ($q) => $q->where('mission_id', $missionId)),
            ],
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }
        $validated = $request->validate($rules);

        $utilisateur->loadMissing('role');

        if ((int) $utilisateur->role_id === (int) $adminRoleId && (int) $validated['role_id'] !== (int) $adminRoleId) {
            $autresAdmins = User::query()
                ->where('mission_id', $missionId)
                ->where('role_id', $adminRoleId)
                ->whereKeyNot($utilisateur->id)
                ->count();
            if ($autresAdmins === 0) {
                return back()->withInput()->withErrors([
                    'role_id' => 'Conservez au moins un administrateur mission.',
                ]);
            }
        }

        $role = Role::query()->findOrFail($validated['role_id']);
        $egliseId = $this->roleRequiresEglise($role)
            ? (int) $validated['eglise_locale_id']
            : null;

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mission_id' => $missionId,
            'eglise_locale_id' => $egliseId,
            'role_id' => $role->id,
        ];

        if ($request->filled('password')) {
            $data['password'] = $validated['password'];
        }

        $utilisateur->update($data);

        return redirect()
            ->route('parametres.utilisateurs.edit', $utilisateur)
            ->with('success', __('flash.utilisateur_saved'));
    }

    public function destroy(Request $request, User $utilisateur): RedirectResponse
    {
        $this->authorize('delete', $utilisateur);

        $missionId = (int) $request->user()->mission_id;
        $adminRoleId = Role::idFor('admin_mission');

        if ((int) $utilisateur->role_id === (int) $adminRoleId) {
            $autresAdmins = User::query()
                ->where('mission_id', $missionId)
                ->where('role_id', $adminRoleId)
                ->whereKeyNot($utilisateur->id)
                ->count();
            if ($autresAdmins === 0) {
                return redirect()
                    ->route('parametres.utilisateurs.index')
                    ->with('error', __('flash.utilisateur_last_admin'));
            }
        }

        $utilisateur->delete();

        return redirect()
            ->route('parametres.utilisateurs.index')
            ->with('success', __('flash.utilisateur_deleted'));
    }
}
