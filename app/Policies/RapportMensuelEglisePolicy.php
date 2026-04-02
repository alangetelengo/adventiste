<?php

namespace App\Policies;

use App\Models\RapportMensuelEglise;
use App\Models\User;

class RapportMensuelEglisePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finances.rapports.view_any')
            && ($user->eglise_locale_id !== null || $user->mission_id !== null);
    }

    public function view(User $user, RapportMensuelEglise $rapport): bool
    {
        if (! $user->hasPermission('finances.rapports.view_any')) {
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
        return $user->hasPermission('finances.rapports.create')
            && $user->eglise_locale_id !== null;
    }

    public function update(User $user, RapportMensuelEglise $rapport): bool
    {
        if (! $user->hasPermission('finances.rapports.update')) {
            return false;
        }

        if ($user->mission_id !== null && $user->eglise_locale_id === null) {
            return false;
        }

        return $user->eglise_locale_id !== null
            && (int) $rapport->eglise_locale_id === (int) $user->eglise_locale_id
            && in_array($rapport->etat_transmission, [RapportMensuelEglise::ETAT_BROUILLON, RapportMensuelEglise::ETAT_REFUSE_MISSION], true);
    }

    public function delete(User $user, RapportMensuelEglise $rapport): bool
    {
        return $this->update($user, $rapport)
            && $user->hasPermission('finances.rapports.delete')
            && $rapport->verrouille_le === null;
    }

    public function soumettre(User $user, RapportMensuelEglise $rapport): bool
    {
        return $user->hasPermission('finances.rapports.soumettre')
            && $user->eglise_locale_id !== null
            && (int) $rapport->eglise_locale_id === (int) $user->eglise_locale_id
            && in_array($rapport->etat_transmission, [RapportMensuelEglise::ETAT_BROUILLON, RapportMensuelEglise::ETAT_REFUSE_MISSION], true);
    }

    public function reviewMission(User $user, RapportMensuelEglise $rapport): bool
    {
        if (! $user->hasPermission('finances.rapports.review_mission')) {
            return false;
        }

        if (! $user->estUtilisateurMission() || $user->mission_id === null) {
            return false;
        }

        return (int) $rapport->egliseLocale->mission_id === (int) $user->mission_id
            && $rapport->etat_transmission === RapportMensuelEglise::ETAT_SOUMIS;
    }
}
