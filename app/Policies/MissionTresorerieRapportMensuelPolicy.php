<?php

namespace App\Policies;

use App\Models\MissionTresorerieRapportMensuel;
use App\Models\User;

class MissionTresorerieRapportMensuelPolicy
{
    /**
     * Liste / exports « État des dîmes » et « Synthèse annuelle » (mission).
     */
    public function viewAny(User $user): bool
    {
        return $this->peutConsulterFinancesMission($user);
    }

    /**
     * Fiches mensuelles du module Ventilation trésorerie (saisie, workflow).
     */
    public function view(User $user, MissionTresorerieRapportMensuel $rapport): bool
    {
        return $this->peutConsulterVentilation($user)
            && (int) $rapport->mission_id === (int) $user->mission_id;
    }

    /**
     * Accès au menu et aux routes du module Ventilation trésorerie mission.
     */
    public function ventilationModule(User $user): bool
    {
        return $this->peutConsulterVentilation($user);
    }

    public function update(User $user, MissionTresorerieRapportMensuel $rapport): bool
    {
        return $user->hasPermission('finances.ventilation_tresorerie_mission.update')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null
            && (int) $rapport->mission_id === (int) $user->mission_id
            && in_array(
                (string) ($rapport->etat_transmission ?? MissionTresorerieRapportMensuel::ETAT_BROUILLON),
                [MissionTresorerieRapportMensuel::ETAT_BROUILLON, MissionTresorerieRapportMensuel::ETAT_REFUSE_MISSION],
                true
            );
    }

    public function soumettre(User $user, MissionTresorerieRapportMensuel $rapport): bool
    {
        return $user->hasPermission('finances.ventilation_tresorerie_mission.soumettre')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null
            && (int) $rapport->mission_id === (int) $user->mission_id
            && in_array(
                (string) ($rapport->etat_transmission ?? MissionTresorerieRapportMensuel::ETAT_BROUILLON),
                [MissionTresorerieRapportMensuel::ETAT_BROUILLON, MissionTresorerieRapportMensuel::ETAT_REFUSE_MISSION],
                true
            );
    }

    public function reviewMission(User $user, MissionTresorerieRapportMensuel $rapport): bool
    {
        return $user->hasPermission('finances.ventilation_tresorerie_mission.review_mission')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null
            && (int) $rapport->mission_id === (int) $user->mission_id
            && (string) $rapport->etat_transmission === MissionTresorerieRapportMensuel::ETAT_SOUMIS;
    }

    private function peutConsulterFinancesMission(User $user): bool
    {
        return $user->hasPermission('finances.mission_finances_consultation.view')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null;
    }

    private function peutConsulterVentilation(User $user): bool
    {
        return $user->hasPermission('finances.ventilation_tresorerie_mission.view')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null;
    }
}
