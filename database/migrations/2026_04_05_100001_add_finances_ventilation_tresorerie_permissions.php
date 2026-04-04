<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            ['name' => 'finances.ventilation_tresorerie_mission.view', 'label' => 'Finances — Rapport ventilation trésorerie mission', 'group' => 'finances'],
            ['name' => 'finances.ventilation_tresorerie_mission.update', 'label' => 'Finances — Saisir le rapport ventilation trésorerie mission', 'group' => 'finances'],
            ['name' => 'finances.mission_finances_consultation.view', 'label' => 'Finances — Consulter état des dîmes et synthèse annuelle (mission)', 'group' => 'finances'],
        ];
        foreach ($rows as $row) {
            DB::table('permissions')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $permIds = DB::table('permissions')->whereIn('name', array_column($rows, 'name'))->pluck('id', 'name')->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        foreach (['tresorier_mission', 'president_mission', 'admin_mission'] as $rn) {
            if (! isset($roleIds[$rn])) {
                continue;
            }
            DB::table('permission_role')->insert([
                'permission_id' => $permIds['finances.ventilation_tresorerie_mission.view'],
                'role_id' => $roleIds[$rn],
            ]);
        }

        foreach (['tresorier_mission', 'president_mission', 'secretaire_executif_mission', 'admin_mission'] as $rn) {
            if (! isset($roleIds[$rn])) {
                continue;
            }
            DB::table('permission_role')->insert([
                'permission_id' => $permIds['finances.mission_finances_consultation.view'],
                'role_id' => $roleIds[$rn],
            ]);
        }

        foreach (['tresorier_mission', 'admin_mission'] as $rn) {
            if (! isset($roleIds[$rn])) {
                continue;
            }
            DB::table('permission_role')->insert([
                'permission_id' => $permIds['finances.ventilation_tresorerie_mission.update'],
                'role_id' => $roleIds[$rn],
            ]);
        }
    }

    public function down(): void
    {
        $names = [
            'finances.ventilation_tresorerie_mission.view',
            'finances.ventilation_tresorerie_mission.update',
            'finances.mission_finances_consultation.view',
        ];
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
