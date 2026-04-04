<?php

namespace App\Policies;

use App\Models\RapportStationMission;
use App\Models\User;

class RapportStationMissionPolicy
{
    private function peutAccederMission(User $user): bool
    {
        if (! $user->estUtilisateurMission() || $user->mission_id === null) {
            return false;
        }

        if ($user->estAdministrateurMission()) {
            return true;
        }

        return $user->hasPermission('finances.rapports.view_any');
    }

    public function viewAny(User $user): bool
    {
        return $this->peutAccederMission($user);
    }

    public function view(User $user, RapportStationMission $rapport): bool
    {
        return $this->peutAccederMission($user)
            && $user->mission_id === $rapport->mission_id;
    }

    public function create(User $user): bool
    {
        return $this->peutAccederMission($user);
    }

    public function update(User $user, RapportStationMission $rapport): bool
    {
        return $this->peutAccederMission($user)
            && $user->mission_id === $rapport->mission_id;
    }

    public function delete(User $user, RapportStationMission $rapport): bool
    {
        return $this->peutAccederMission($user)
            && $user->mission_id === $rapport->mission_id;
    }
}
