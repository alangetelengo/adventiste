<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Bases déjà migrées avec l’ancienne version de 2026_04_05_100001 :
 * ajoute la consultation « état dîmes / synthèse » et retire la ventilation trésorerie au secrétaire exécutif de mission.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $name = 'finances.mission_finances_consultation.view';

        if (! DB::table('permissions')->where('name', $name)->exists()) {
            DB::table('permissions')->insert([
                'name' => $name,
                'label' => 'Finances — Consulter état des dîmes et synthèse annuelle (mission)',
                'group' => 'finances',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $consultId = (int) DB::table('permissions')->where('name', $name)->value('id');
        $ventViewId = (int) DB::table('permissions')->where('name', 'finances.ventilation_tresorerie_mission.view')->value('id');
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        foreach (['president_mission', 'tresorier_mission', 'admin_mission', 'secretaire_executif_mission'] as $rn) {
            if (! isset($roleIds[$rn]) || $consultId === 0) {
                continue;
            }
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $consultId,
                'role_id' => $roleIds[$rn],
            ]);
        }

        if ($ventViewId > 0 && isset($roleIds['secretaire_executif_mission'])) {
            DB::table('permission_role')
                ->where('permission_id', $ventViewId)
                ->where('role_id', $roleIds['secretaire_executif_mission'])
                ->delete();
        }
    }

    public function down(): void
    {
        // Ne pas supprimer la permission ni ré-attacher la ventilation au secrétaire : risque en production.
    }
};
