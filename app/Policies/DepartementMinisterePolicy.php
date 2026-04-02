<?php

namespace App\Policies;

use App\Models\DepartementMinistere;
use App\Models\User;

class DepartementMinisterePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('parametres.departements.view_any');
    }

    public function view(User $user, DepartementMinistere $departement): bool
    {
        return $user->hasPermission('parametres.departements.view_any')
            && $user->mission_id === $departement->egliseLocale->mission_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('parametres.departements.create');
    }

    public function update(User $user, DepartementMinistere $departement): bool
    {
        return $user->hasPermission('parametres.departements.update')
            && $user->mission_id === $departement->egliseLocale->mission_id;
    }

    public function delete(User $user, DepartementMinistere $departement): bool
    {
        return $user->hasPermission('parametres.departements.delete')
            && $user->mission_id === $departement->egliseLocale->mission_id;
    }
}
