<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Gestion des comptes : réservée aux administrateurs mission.
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasPermission('parametres.utilisateurs.manage');
    }

    public function view(User $actor, User $model): bool
    {
        return $actor->hasPermission('parametres.utilisateurs.manage')
            && $this->memeMission($actor, $model);
    }

    public function create(User $actor): bool
    {
        return $actor->hasPermission('parametres.utilisateurs.manage');
    }

    public function update(User $actor, User $model): bool
    {
        return $actor->hasPermission('parametres.utilisateurs.manage')
            && $this->memeMission($actor, $model);
    }

    public function delete(User $actor, User $model): bool
    {
        return $actor->hasPermission('parametres.utilisateurs.manage')
            && $this->memeMission($actor, $model)
            && $actor->id !== $model->id;
    }

    private function memeMission(User $actor, User $model): bool
    {
        if ($actor->mission_id === null || $model->mission_id === null) {
            return false;
        }

        return (int) $actor->mission_id === (int) $model->mission_id;
    }
}
