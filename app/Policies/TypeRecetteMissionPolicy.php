<?php

namespace App\Policies;

use App\Models\TypeRecetteMission;
use App\Models\User;

class TypeRecetteMissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('parametres.types_recette.view')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null;
    }

    public function view(User $user, TypeRecetteMission $type): bool
    {
        return $this->viewAny($user)
            && (int) $type->mission_id === (int) $user->mission_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('parametres.types_recette.update')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null;
    }

    public function update(User $user, TypeRecetteMission $type): bool
    {
        return $this->create($user)
            && (int) $type->mission_id === (int) $user->mission_id;
    }

    public function delete(User $user, TypeRecetteMission $type): bool
    {
        return $this->update($user, $type);
    }
}
