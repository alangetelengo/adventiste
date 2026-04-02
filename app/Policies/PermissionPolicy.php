<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('parametres.permissions.manage');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('parametres.permissions.manage');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $this->create($user);
    }
}
