<?php

namespace App\Policies;

use App\Models\RapportStationMission;
use App\Models\User;

class RapportStationMissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->mission_id !== null;
    }

    public function view(User $user, RapportStationMission $rapport): bool
    {
        return $user->mission_id === $rapport->mission_id;
    }

    public function create(User $user): bool
    {
        return $user->mission_id !== null;
    }

    public function update(User $user, RapportStationMission $rapport): bool
    {
        return $user->mission_id === $rapport->mission_id;
    }

    public function delete(User $user, RapportStationMission $rapport): bool
    {
        return $user->mission_id === $rapport->mission_id;
    }
}
