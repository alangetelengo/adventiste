<?php

namespace App\Policies;

use App\Models\RapportMembreEglise;
use App\Models\User;

class RapportMembreEglisePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('secretariat.rapports_membres.view_any')
            && ($user->eglise_locale_id !== null || $user->mission_id !== null);
    }

    public function view(User $user, RapportMembreEglise $rapport): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->eglise_locale_id !== null) {
            return (int) $rapport->eglise_locale_id === (int) $user->eglise_locale_id;
        }

        if ($user->mission_id !== null) {
            return (int) $rapport->egliseLocale->mission_id === (int) $user->mission_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('secretariat.rapports_membres.create')
            && $user->eglise_locale_id !== null;
    }

    public function update(User $user, RapportMembreEglise $rapport): bool
    {
        return $user->hasPermission('secretariat.rapports_membres.update')
            && $user->eglise_locale_id !== null
            && (int) $rapport->eglise_locale_id === (int) $user->eglise_locale_id
            && in_array($rapport->etat, [RapportMembreEglise::ETAT_BROUILLON, RapportMembreEglise::ETAT_REJETE], true);
    }

    public function delete(User $user, RapportMembreEglise $rapport): bool
    {
        return $user->hasPermission('secretariat.rapports_membres.delete')
            && $user->eglise_locale_id !== null
            && (int) $rapport->eglise_locale_id === (int) $user->eglise_locale_id
            && in_array($rapport->etat, [RapportMembreEglise::ETAT_BROUILLON, RapportMembreEglise::ETAT_REJETE], true);
    }

    public function soumettre(User $user, RapportMembreEglise $rapport): bool
    {
        return $user->hasPermission('secretariat.rapports_membres.soumettre')
            && $user->eglise_locale_id !== null
            && (int) $rapport->eglise_locale_id === (int) $user->eglise_locale_id
            && in_array($rapport->etat, [RapportMembreEglise::ETAT_BROUILLON, RapportMembreEglise::ETAT_REJETE], true);
    }

    public function review(User $user, RapportMembreEglise $rapport): bool
    {
        return $user->hasPermission('secretariat.rapports_membres.review')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null
            && (int) $rapport->egliseLocale->mission_id === (int) $user->mission_id
            && $rapport->etat === RapportMembreEglise::ETAT_SOUMIS;
    }
}
