<?php

namespace App\Policies;

use App\Models\District;
use App\Models\User;

class DistrictPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->estUtilisateurMission()
            && $user->hasPermission('parametres.districts.view_any');
    }

    public function view(User $user, District $district): bool
    {
        if (! $user->estUtilisateurMission()) {
            return false;
        }

        return $user->hasPermission('parametres.districts.view_any')
            && (int) $district->mission_id === (int) $user->mission_id;
    }

    public function create(User $user): bool
    {
        return $user->estUtilisateurMission()
            && $user->hasPermission('parametres.districts.create');
    }

    public function update(User $user, District $district): bool
    {
        return $user->hasPermission('parametres.districts.update')
            && $this->view($user, $district);
    }

    public function delete(User $user, District $district): bool
    {
        return $user->hasPermission('parametres.districts.delete')
            && $this->update($user, $district);
    }
}
