<?php

namespace App\Policies;

use App\Models\MissionTresorerieRapportMensuel;
use App\Models\User;

class MissionTresorerieRapportMensuelPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->peutConsulter($user);
    }

    public function view(User $user, MissionTresorerieRapportMensuel $rapport): bool
    {
        return $this->peutConsulter($user)
            && (int) $rapport->mission_id === (int) $user->mission_id;
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

    private function peutConsulter(User $user): bool
    {
        return $user->hasPermission('finances.ventilation_tresorerie_mission.view')
            && $user->estUtilisateurMission()
            && $user->mission_id !== null;
    }
}
