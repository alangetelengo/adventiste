<?php

namespace App\Policies;

use App\Models\Membre;
use App\Models\User;

class MembrePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canRead($user)
            && ($user->eglise_locale_id !== null || $user->mission_id !== null);
    }

    public function view(User $user, Membre $membre): bool
    {
        if (! $this->canRead($user)) {
            return false;
        }

        return $this->membreDansPérimètre($user, $membre);
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('tresorier_eglise') || $user->hasRole('tresorier_mission')) {
            return false;
        }

        return $this->canCreate($user)
            && ($user->eglise_locale_id !== null || $user->mission_id !== null);
    }

    public function update(User $user, Membre $membre): bool
    {
        if (! $this->canWrite($user)) {
            return false;
        }

        return $this->membreDansPérimètre($user, $membre);
    }

    public function delete(User $user, Membre $membre): bool
    {
        if (! $this->canWrite($user)) {
            return false;
        }

        return $this->membreDansPérimètre($user, $membre);
    }

    public function changeStatut(User $user, Membre $membre): bool
    {
        if (! $this->canChangeStatut($user)) {
            return false;
        }

        return $this->membreDansPérimètre($user, $membre);
    }

    private function membreDansPérimètre(User $user, Membre $membre): bool
    {
        if ($user->eglise_locale_id !== null) {
            return (int) $membre->eglise_locale_id === (int) $user->eglise_locale_id;
        }

        if ($user->mission_id !== null) {
            if ($membre->eglise_locale_id === null) {
                return false;
            }

            return $membre->relationLoaded('egliseLocale')
                ? (int) $membre->egliseLocale->mission_id === (int) $user->mission_id
                : $membre->egliseLocale()->where('mission_id', $user->mission_id)->exists();
        }

        return false;
    }

    private function canRead(User $user): bool
    {
        return $user->hasPermission('membres.view_any')
            || $user->hasRole('secretaire_eglise')
            || $user->hasRole('secretaire');
    }

    private function canWrite(User $user): bool
    {
        return $user->hasPermission('membres.create')
            || $user->hasPermission('membres.update')
            || $user->hasPermission('membres.delete')
            || $user->hasRole('secretaire_eglise')
            || $user->hasRole('secretaire');
    }

    private function canCreate(User $user): bool
    {
        return $user->hasPermission('membres.create')
            || $user->hasRole('secretaire_eglise')
            || $user->hasRole('secretaire');
    }

    private function canChangeStatut(User $user): bool
    {
        if ($user->hasRole('tresorier_eglise') || $user->hasRole('tresorier_mission')) {
            return false;
        }

        return $user->hasPermission('membres.change_statut')
            || $user->hasRole('secretaire_eglise')
            || $user->hasRole('secretaire')
            || $user->hasRole('secretaire_executif_mission');
    }
}
