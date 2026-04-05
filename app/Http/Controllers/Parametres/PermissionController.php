<?php

namespace App\Http\Controllers\Parametres;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Permission::class);

        $permissions = Permission::query()
            ->withCount('roles')
            ->orderBy('group')
            ->orderBy('label')
            ->get();

        return view('parametres.permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        $this->authorize('create', Permission::class);

        return view('parametres.permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Permission::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9._-]+$/', 'unique:permissions,name'],
            'label' => ['required', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:80'],
        ]);

        Permission::query()->create($validated);

        return redirect()
            ->route('parametres.permissions.index')
            ->with('success', __('flash.permission_created'));
    }

    public function edit(Permission $permission): View
    {
        $this->authorize('update', $permission);

        return view('parametres.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $this->authorize('update', $permission);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9._-]+$/',
                Rule::unique('permissions', 'name')->ignore($permission->id),
            ],
            'label' => ['required', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:80'],
        ]);

        $permission->update($validated);

        return redirect()
            ->route('parametres.permissions.edit', $permission)
            ->with('success', __('flash.permission_saved'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->authorize('delete', $permission);

        if ($permission->roles()->exists()) {
            return redirect()
                ->route('parametres.permissions.index')
                ->with('error', __('flash.permission_roles_blocked'));
        }

        $permission->delete();

        return redirect()
            ->route('parametres.permissions.index')
            ->with('success', __('flash.permission_deleted'));
    }
}
