<?php

namespace App\Policies;

use App\Models\EgliseLocale;
use App\Models\User;

class EgliseLocalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->estUtilisateurMission()
            && $user->hasPermission('parametres.eglises.view_any');
    }

    public function view(User $user, EgliseLocale $eglise): bool
    {
        if (! $user->estUtilisateurMission()) {
            return false;
        }

        return $user->hasPermission('parametres.eglises.view_any')
            && (int) $eglise->mission_id === (int) $user->mission_id;
    }

    public function create(User $user): bool
    {
        return $user->estUtilisateurMission()
            && $user->hasPermission('parametres.eglises.create');
    }

    public function update(User $user, EgliseLocale $eglise): bool
    {
        return $user->hasPermission('parametres.eglises.update')
            && $this->view($user, $eglise);
    }

    public function delete(User $user, EgliseLocale $eglise): bool
    {
        return $user->hasPermission('parametres.eglises.delete')
            && $this->update($user, $eglise);
    }
}
