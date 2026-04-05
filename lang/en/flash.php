<?php

return [
    'bapteme_stored' => 'Baptism recorded.',
    'bapteme_updated' => 'Baptism updated.',
    'bapteme_deleted' => 'Baptism deleted.',

    'membre_stored_transfer' => 'Transferred member recorded and activated.',
    'membre_stored' => 'Member recorded.',
    'membre_updated_transfer' => 'Member record updated (transfer active).',
    'membre_updated' => 'Member record updated.',
    'membre_status_unchanged' => 'The member already has this status.',
    'membre_status_updated' => 'Member status updated.',
    'membre_deleted' => 'Member deleted.',
    'membre_mission_not_found' => 'No mission found for this member.',

    'ventilation_stored' => 'Treasury ventilation report saved.',
    'ventilation_submitted' => 'Report submitted for validation.',
    'ventilation_validated' => 'Report validated.',
    'ventilation_refused' => 'Report refused with comment.',

    'rapport_mensuel_exists' => 'A report already exists for this period. Here is its record.',
    'rapport_mensuel_generated' => 'Monthly report generated from the month’s recaps.',
    'rapport_mensuel_locked_signatures' => 'This report is locked: signatures can no longer be changed here.',
    'rapport_mensuel_locked' => 'Report locked.',
    'rapport_mensuel_saved' => 'Report saved.',
    'rapport_mensuel_delete_locked' => 'Cannot delete a locked report.',
    'rapport_mensuel_deleted' => 'Report deleted.',
    'rapport_mensuel_regen_locked' => 'Cannot regenerate while the report is locked.',
    'rapport_mensuel_regenerated' => 'Totals and lines recalculated from recaps.',
    'rapport_mensuel_submitted' => 'Monthly report submitted to the mission.',
    'rapport_mensuel_validated' => 'Monthly report validated by the mission.',
    'rapport_mensuel_refused' => 'Monthly report refused with comment.',

    'password_updated' => 'Your password has been updated.',

    'recap_sabbat_saved' => 'Sabbath saved successfully. You can edit it later if needed.',
    'recap_saved' => 'Recap saved.',
    'recap_submit_blocked' => 'Cannot submit: fix refused lines, or wait for validation of lines already submitted.',
    'recap_submitted' => 'Recap submitted to the mission. Lines are pending validation.',
    'recap_accept_blocked' => 'This recap cannot be accepted (not all lines are “submitted”).',
    'recap_accepted' => 'Recap accepted: lines are locked.',
    'recap_refuse_blocked' => 'This recap cannot be refused in its current state.',
    'recap_refused' => 'Recap refused: the church can correct the affected lines.',
    'recette_eglise_saved' => 'Church receipt recorded. It will be included in the monthly report for the period.',

    'synthese_transfers_table_missing' => 'The bank transfers table is missing. Run migrations first.',
    'synthese_transfers_saved' => 'Bank transfers saved.',

    'type_recette_created' => 'Receipt type created.',
    'type_recette_updated' => 'Receipt type updated.',
    'type_recette_in_use' => 'This type is used in recaps: deactivate it rather than deleting.',
    'type_recette_deleted' => 'Receipt type deleted.',

    'notifications_read' => 'Notifications marked as read.',

    'rapport_membres_exists' => 'A report already exists for this period.',
    'rapport_membres_generated' => 'Member report generated.',
    'rapport_membres_updated' => 'Report updated.',
    'rapport_membres_deleted' => 'Report deleted.',
    'rapport_membres_submitted' => 'Report submitted to the mission.',
    'rapport_membres_validated' => 'Report validated by the mission.',
    'rapport_membres_rejected' => 'Report rejected with comment.',

    'type_statut_created' => 'Member status type created.',
    'type_statut_updated' => 'Member status type updated.',
    'type_statut_system_delete' => 'System status types cannot be deleted.',
    'type_statut_in_use' => 'This type is already in use: deactivate it rather than deleting.',
    'type_statut_deleted' => 'Member status type deleted.',

    'departement_created' => 'Department/ministry created.',
    'departement_saved' => 'Department/ministry saved.',
    'departement_collectes_blocked' => 'Cannot delete: collections are linked to this department. Remove them first.',
    'departement_deleted' => 'Department/ministry deleted.',

    'rapport_station_created' => 'Station report created.',
    'rapport_station_saved' => 'Station report saved.',
    'rapport_station_deleted' => 'Station report deleted.',

    'entree_financiere_saved' => 'Financial entry saved.',
    'entree_financiere_deleted' => 'Financial entry deleted.',

    'tresorerie_ventilation_lignes_saved' => 'Ventilation lines saved.',

    'ventilation_recettes_saved' => 'Ventilation rules saved. They apply to recaps, monthly reports, and future annual reports.',

    'utilisateur_created' => 'User created.',
    'utilisateur_saved' => 'User saved.',
    'utilisateur_last_admin' => 'Cannot delete the last mission administrator.',
    'utilisateur_deleted' => 'User deleted.',

    'groupe_mission_created' => 'Mission group created.',
    'groupe_mission_saved' => 'Mission group saved.',
    'groupe_mission_membres_blocked' => 'Cannot delete: members are still linked to this group. Remove the group from member records first.',
    'groupe_mission_finances_blocked' => 'Cannot delete: mission finance lines exist for this group.',
    'groupe_mission_deleted' => 'Mission group deleted.',

    'district_created' => 'District created.',
    'district_saved' => 'District saved.',
    'district_deleted' => 'District deleted. Linked churches no longer have a district (field cleared).',

    'eglise_created' => 'Local church created.',
    'eglise_saved' => 'Local church saved.',
    'eglise_deleted' => 'Local church deleted (linked data cascades per database).',

    'permission_created' => 'Permission created. Remember to assign it to one or more roles.',
    'permission_saved' => 'Permission saved.',
    'permission_roles_blocked' => 'Remove this permission from roles before deleting it.',
    'permission_deleted' => 'Permission deleted.',

    'role_created' => 'Role created.',
    'role_saved' => 'Role saved.',
    'role_users_blocked' => 'Cannot delete this role: users are still assigned to it.',
    'role_deleted' => 'Role deleted.',
];
