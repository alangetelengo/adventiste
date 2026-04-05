<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::query()
            ->withCount('users')
            ->orderBy('is_system', 'desc')
            ->orderBy('label')
            ->get();

        return view('parametres.roles.index', compact('roles'));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::query()->orderBy('group')->orderBy('label')->get();

        return view('parametres.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9._-]+$/', 'unique:roles,name'],
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::query()->create([
            'name' => $validated['name'],
            'label' => $validated['label'],
            'is_system' => false,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()
            ->route('parametres.roles.edit', $role)
            ->with('success', __('flash.role_created'));
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $role->load('permissions');
        $permissions = Permission::query()->orderBy('group')->orderBy('label')->get();

        return view('parametres.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $rules = [
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];

        if (! $role->is_system) {
            $rules['name'] = [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('roles', 'name')->ignore($role->id),
            ];
        }

        $validated = $request->validate($rules);

        $data = ['label' => $validated['label']];
        if (! $role->is_system && isset($validated['name'])) {
            $data['name'] = $validated['name'];
        }
        $role->update($data);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()
            ->route('parametres.roles.edit', $role)
            ->with('success', __('flash.role_saved'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->users()->exists()) {
            return redirect()
                ->route('parametres.roles.index')
                ->with('error', __('flash.role_users_blocked'));
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('parametres.roles.index')
            ->with('success', __('flash.role_deleted'));
    }
}
