<?php

namespace App\Policies;

use App\Models\MissionReglesVentilationRecettes;
use App\Models\User;

class MissionReglesVentilationRecettesPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->estUtilisateurMission()
            && $user->mission_id !== null
            && $user->hasPermission('parametres.ventilation_recettes.view');
    }

    public function view(User $user, MissionReglesVentilationRecettes $regles): bool
    {
        return $this->viewAny($user)
            && (int) $regles->mission_id === (int) $user->mission_id;
    }

    public function update(User $user, MissionReglesVentilationRecettes $regles): bool
    {
        return $user->hasPermission('parametres.ventilation_recettes.update')
            && $user->estUtilisateurMission()
            && (int) $regles->mission_id === (int) $user->mission_id;
    }
}
