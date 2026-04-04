<?php

namespace App\Policies;

use App\Models\RapportMensuelEglise;
use App\Models\User;

class RapportMensuelEglisePolicy
{
    /** @var list<string> */
    private const ROLES_CONSULTATION = [
        'tresorier_eglise',
        'tresorier_mission',
        'president_mission',
        'admin_mission',
    ];

    /** @var list<string> */
    private const ROLES_VALIDATION_MISSION = [
        'tresorier_mission',
        'president_mission',
        'admin_mission',
    ];

    private function roleParmi(User $user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    private function peutConsulter(User $user): bool
    {
        if (! $user->hasPermission('finances.rapports.view_any')) {
            return false;
        }

        if ($user->eglise_locale_id === null && $user->mission_id === null) {
            return false;
        }

        return $this->roleParmi($user, self::ROLES_CONSULTATION);
    }

    public function viewAny(User $user): bool
    {
        return $this->peutConsulter($user);
    }

    public function view(User $user, RapportMensuelEglise $rapport): bool
    {
        if (! $this->peutConsulter($user)) {
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

    /**
     * Création / régénération / signatures / soumission : uniquement le trésorier d’église locale.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('finances.rapports.create')
            && $user->eglise_locale_id !== null
            && $user->hasRole('tresorier_eglise');
    }

    public function update(User $user, RapportMensuelEglise $rapport): bool
    {
        if (! $user->hasPermission('finances.rapports.update')) {
            return false;
        }

        if (! $user->hasRole('tresorier_eglise')) {
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
            && $user->hasRole('tresorier_eglise')
            && $user->eglise_locale_id !== null
            && (int) $rapport->eglise_locale_id === (int) $user->eglise_locale_id
            && in_array($rapport->etat_transmission, [RapportMensuelEglise::ETAT_BROUILLON, RapportMensuelEglise::ETAT_REFUSE_MISSION], true);
    }

    /**
     * Validation côté mission : trésorier de mission, président ou administrateur (pas le secrétaire exécutif).
     */
    public function reviewMission(User $user, RapportMensuelEglise $rapport): bool
    {
        if (! $user->hasPermission('finances.rapports.review_mission')) {
            return false;
        }

        if (! $user->estUtilisateurMission() || $user->mission_id === null) {
            return false;
        }

        if (! $this->roleParmi($user, self::ROLES_VALIDATION_MISSION)) {
            return false;
        }

        return (int) $rapport->egliseLocale->mission_id === (int) $user->mission_id
            && $rapport->etat_transmission === RapportMensuelEglise::ETAT_SOUMIS;
    }
}
