<?php

namespace App\Policies;

use App\Models\Bapteme;
use App\Models\User;

class BaptemePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canRead($user)
            && ($user->eglise_locale_id !== null || $user->mission_id !== null);
    }

    public function view(User $user, Bapteme $bapteme): bool
    {
        if (! $this->canRead($user)) {
            return false;
        }

        return $this->dansPerimetre($user, $bapteme);
    }

    public function create(User $user): bool
    {
        return $this->canWrite($user)
            && ($user->eglise_locale_id !== null || $user->mission_id !== null);
    }

    public function update(User $user, Bapteme $bapteme): bool
    {
        if (! $this->canWrite($user)) {
            return false;
        }

        return $this->dansPerimetre($user, $bapteme);
    }

    public function delete(User $user, Bapteme $bapteme): bool
    {
        if (! $this->canWrite($user)) {
            return false;
        }

        return $this->dansPerimetre($user, $bapteme);
    }

    public function certificat(User $user, Bapteme $bapteme): bool
    {
        if (! $this->canRead($user) && ! $user->hasPermission('baptemes.certificat')) {
            return false;
        }

        return $this->dansPerimetre($user, $bapteme);
    }

    private function dansPerimetre(User $user, Bapteme $bapteme): bool
    {
        if ($user->eglise_locale_id !== null) {
            return (int) $bapteme->eglise_locale_id === (int) $user->eglise_locale_id;
        }

        if ($user->mission_id !== null) {
            return $bapteme->relationLoaded('egliseLocale')
                ? (int) $bapteme->egliseLocale->mission_id === (int) $user->mission_id
                : $bapteme->egliseLocale()->where('mission_id', $user->mission_id)->exists();
        }

        return false;
    }

    private function canRead(User $user): bool
    {
        return $user->hasPermission('baptemes.view_any')
            || $user->hasPermission('baptemes.certificat')
            || $user->hasRole('secretaire_eglise')
            || $user->hasRole('secretaire');
    }

    private function canWrite(User $user): bool
    {
        return $user->hasPermission('baptemes.create')
            || $user->hasPermission('baptemes.update')
            || $user->hasPermission('baptemes.delete')
            || $user->hasRole('secretaire_eglise')
            || $user->hasRole('secretaire');
    }
}

