<?php

return [
    'bapteme_stored' => 'Baptême enregistré.',
    'bapteme_updated' => 'Baptême mis à jour.',
    'bapteme_deleted' => 'Baptême supprimé.',

    'membre_stored_transfer' => 'Membre transféré enregistré et activé.',
    'membre_stored' => 'Membre enregistré.',
    'membre_updated_transfer' => 'Fiche membre mise à jour (transfert activé).',
    'membre_updated' => 'Fiche membre mise à jour.',
    'membre_status_unchanged' => 'Le membre est déjà dans ce statut.',
    'membre_status_updated' => 'Statut du membre mis à jour.',
    'membre_deleted' => 'Membre supprimé.',
    'membre_mission_not_found' => 'Mission introuvable pour ce membre.',

    'ventilation_stored' => 'Rapport de ventilation trésorerie enregistré.',
    'ventilation_submitted' => 'Rapport soumis pour validation.',
    'ventilation_validated' => 'Rapport validé.',
    'ventilation_refused' => 'Rapport refusé avec commentaire.',

    'rapport_mensuel_exists' => 'Un rapport existe déjà pour cette période. Voici sa fiche.',
    'rapport_mensuel_generated' => 'Rapport mensuel généré à partir des récaps du mois.',
    'rapport_mensuel_locked_signatures' => 'Ce rapport est verrouillé : les signatures ne peuvent plus être modifiées ici.',
    'rapport_mensuel_locked' => 'Rapport verrouillé.',
    'rapport_mensuel_saved' => 'Rapport enregistré.',
    'rapport_mensuel_delete_locked' => 'Impossible de supprimer un rapport verrouillé.',
    'rapport_mensuel_deleted' => 'Rapport supprimé.',
    'rapport_mensuel_regen_locked' => 'Régénération impossible tant que le rapport est verrouillé.',
    'rapport_mensuel_regenerated' => 'Totaux et lignes recalculés à partir des récaps.',
    'rapport_mensuel_submitted' => 'Rapport mensuel soumis à la mission.',
    'rapport_mensuel_validated' => 'Rapport mensuel validé par la mission.',
    'rapport_mensuel_refused' => 'Rapport mensuel refusé avec commentaire.',

    'password_updated' => 'Votre mot de passe a été mis à jour.',

    'recap_sabbat_saved' => 'Sabbat enregistré avec succès. Vous pourrez le modifier plus tard si nécessaire.',
    'recap_saved' => 'Récap enregistré.',
    'recap_submit_blocked' => 'Impossible de soumettre : corrigez les lignes refusées, ou attendez la validation des lignes déjà soumises.',
    'recap_submitted' => 'Récap soumis à la mission. Les lignes sont en attente de validation.',
    'recap_accept_blocked' => 'Ce récap ne peut pas être accepté (lignes non toutes « soumises »).',
    'recap_accepted' => 'Récap accepté : les lignes sont verrouillées.',
    'recap_refuse_blocked' => 'Ce récap ne peut pas être refusé dans son état actuel.',
    'recap_refused' => 'Récap refusé : l’église peut corriger les lignes concernées.',
    'recette_eglise_saved' => 'Recette de l’église enregistrée. Elle sera prise en compte dans le rapport mensuel de la période.',

    'synthese_transfers_table_missing' => 'La table des transferts bancaires est absente. Lancez d’abord les migrations.',
    'synthese_transfers_saved' => 'Transferts bancaires enregistrés.',

    'type_recette_created' => 'Type de recette créé.',
    'type_recette_updated' => 'Type de recette mis à jour.',
    'type_recette_in_use' => 'Ce type est utilisé dans des récaps : désactivez-le plutôt que le supprimer.',
    'type_recette_deleted' => 'Type de recette supprimé.',

    'notifications_read' => 'Notifications marquées comme lues.',

    'rapport_membres_exists' => 'Un rapport existe déjà pour cette période.',
    'rapport_membres_generated' => 'Rapport membres généré.',
    'rapport_membres_updated' => 'Rapport mis à jour.',
    'rapport_membres_deleted' => 'Rapport supprimé.',
    'rapport_membres_submitted' => 'Rapport soumis à la mission.',
    'rapport_membres_validated' => 'Rapport validé par la mission.',
    'rapport_membres_rejected' => 'Rapport rejeté avec commentaire.',

    'type_statut_created' => 'Type de statut membre créé.',
    'type_statut_updated' => 'Type de statut membre mis à jour.',
    'type_statut_system_delete' => 'Les statuts système ne peuvent pas être supprimés.',
    'type_statut_in_use' => 'Ce type est déjà utilisé : désactivez-le plutôt que le supprimer.',
    'type_statut_deleted' => 'Type de statut membre supprimé.',

    'departement_created' => 'Département/ministère créé.',
    'departement_saved' => 'Département/ministère enregistré.',
    'departement_collectes_blocked' => 'Impossible de supprimer : des collectes sont liées à ce département. Supprimez-les d\'abord.',
    'departement_deleted' => 'Département/ministère supprimé.',

    'rapport_station_created' => 'Rapport de station créé.',
    'rapport_station_saved' => 'Rapport de station enregistré.',
    'rapport_station_deleted' => 'Rapport de station supprimé.',

    'entree_financiere_saved' => 'Entrée financière enregistrée.',
    'entree_financiere_deleted' => 'Entrée financière supprimée.',

    'tresorerie_ventilation_lignes_saved' => 'Lignes de ventilation enregistrées.',

    'ventilation_recettes_saved' => 'Règles de ventilation enregistrées. Elles s’appliquent aux récaps, rapports mensuels et futurs rapports annuels.',

    'utilisateur_created' => 'Utilisateur créé.',
    'utilisateur_saved' => 'Utilisateur enregistré.',
    'utilisateur_last_admin' => 'Impossible de supprimer le dernier administrateur mission.',
    'utilisateur_deleted' => 'Utilisateur supprimé.',

    'groupe_mission_created' => 'Groupe mission créé.',
    'groupe_mission_saved' => 'Groupe mission enregistré.',
    'groupe_mission_membres_blocked' => 'Impossible de supprimer : des membres sont encore rattachés à ce groupe. Retirez le groupe sur les fiches membres d’abord.',
    'groupe_mission_finances_blocked' => 'Impossible de supprimer : des lignes de finances mission existent pour ce groupe.',
    'groupe_mission_deleted' => 'Groupe mission supprimé.',

    'district_created' => 'District créé.',
    'district_saved' => 'District enregistré.',
    'district_deleted' => 'District supprimé. Les églises rattachées n’ont plus de district (champ vidé).',

    'eglise_created' => 'Église locale créée.',
    'eglise_saved' => 'Église locale enregistrée.',
    'eglise_deleted' => 'Église locale supprimée (données liées en cascade selon la base).',

    'permission_created' => 'Permission créée. Pensez à l’associer à un ou plusieurs rôles.',
    'permission_saved' => 'Permission enregistrée.',
    'permission_roles_blocked' => 'Retirez cette permission des rôles avant de la supprimer.',
    'permission_deleted' => 'Permission supprimée.',

    'role_created' => 'Rôle créé.',
    'role_saved' => 'Rôle enregistré.',
    'role_users_blocked' => 'Impossible de supprimer ce rôle : des utilisateurs y sont encore rattachés.',
    'role_deleted' => 'Rôle supprimé.',
];
