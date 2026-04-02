<?php

namespace App\Policies;

use App\Models\TypeStatutMembre;
use App\Models\User;

class TypeStatutMembrePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('parametres.types_statut_membre.view')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null;
    }

    public function view(User $user, TypeStatutMembre $type): bool
    {
        return $this->viewAny($user)
            && (int) $type->mission_id === (int) $user->mission_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('parametres.types_statut_membre.update')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null;
    }

    public function update(User $user, TypeStatutMembre $type): bool
    {
        return $this->create($user)
            && (int) $type->mission_id === (int) $user->mission_id;
    }

    public function delete(User $user, TypeStatutMembre $type): bool
    {
        return $this->update($user, $type);
    }
}
