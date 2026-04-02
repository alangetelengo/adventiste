<?php

namespace App\Policies;

use App\Models\MissionTresorerieVentilationLigne;
use App\Models\User;

class MissionTresorerieVentilationLignePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->peutGerer($user);
    }

    public function update(User $user, MissionTresorerieVentilationLigne $ligne): bool
    {
        return $this->peutGerer($user)
            && (int) $ligne->mission_id === (int) $user->mission_id;
    }

    private function peutGerer(User $user): bool
    {
        return $user->hasPermission('finances.ventilation_tresorerie_mission.update')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null;
    }
}
