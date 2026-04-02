<?php

namespace App\Policies;

use App\Models\GroupeMission;
use App\Models\User;

class GroupeMissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->estUtilisateurMission()
            && $user->hasPermission('parametres.groupes_mission.view_any');
    }

    public function view(User $user, GroupeMission $groupeMission): bool
    {
        if (! $user->estUtilisateurMission()) {
            return false;
        }

        return $user->hasPermission('parametres.groupes_mission.view_any')
            && (int) $groupeMission->mission_id === (int) $user->mission_id;
    }

    public function create(User $user): bool
    {
        return $user->estUtilisateurMission()
            && $user->hasPermission('parametres.groupes_mission.create');
    }

    public function update(User $user, GroupeMission $groupeMission): bool
    {
        return $user->hasPermission('parametres.groupes_mission.update')
            && $this->view($user, $groupeMission);
    }

    public function delete(User $user, GroupeMission $groupeMission): bool
    {
        return $user->hasPermission('parametres.groupes_mission.delete')
            && $this->update($user, $groupeMission);
    }
}
