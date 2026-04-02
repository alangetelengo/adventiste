<?php

namespace App\Policies;

use App\Models\RecapSabbatEglise;
use App\Models\User;

class RecapSabbatEglisePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finances.recaps.view_any')
            && ($user->eglise_locale_id !== null || $user->mission_id !== null);
    }

    public function view(User $user, RecapSabbatEglise $recap): bool
    {
        if (! $user->hasPermission('finances.recaps.view_any')) {
            return false;
        }

        if ($user->eglise_locale_id !== null) {
            return (int) $recap->eglise_locale_id === (int) $user->eglise_locale_id;
        }

        if ($user->mission_id !== null) {
            return (int) $recap->egliseLocale->mission_id === (int) $user->mission_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finances.recaps.create')
            && $user->eglise_locale_id !== null;
    }

    public function update(User $user, RecapSabbatEglise $recap): bool
    {
        if (! $user->hasPermission('finances.recaps.update')) {
            return false;
        }

        if ($user->mission_id !== null && $user->eglise_locale_id === null) {
            return false;
        }

        return $user->eglise_locale_id !== null
            && (int) $recap->eglise_locale_id === (int) $user->eglise_locale_id;
    }

    public function delete(User $user, RecapSabbatEglise $recap): bool
    {
        return $this->update($user, $recap);
    }

    public function soumettre(User $user, RecapSabbatEglise $recap): bool
    {
        return $user->hasPermission('finances.recaps.soumettre')
            && $user->eglise_locale_id !== null
            && (int) $recap->eglise_locale_id === (int) $user->eglise_locale_id;
    }

    public function validerMission(User $user, RecapSabbatEglise $recap): bool
    {
        if (! $user->hasPermission('finances.recaps.valider_mission')) {
            return false;
        }

        if (! $user->estUtilisateurMission() || $user->mission_id === null || $user->eglise_locale_id !== null) {
            return false;
        }

        return (int) $recap->egliseLocale->mission_id === (int) $user->mission_id;
    }
}
