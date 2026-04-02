<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            ['name' => 'parametres.ventilation_recettes.view', 'label' => 'Paramètres — Voir la ventilation des recettes (mission)', 'group' => 'parametres'],
            ['name' => 'parametres.ventilation_recettes.update', 'label' => 'Paramètres — Modifier la ventilation des recettes (mission)', 'group' => 'parametres'],
        ];
        foreach ($rows as $row) {
            DB::table('permissions')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $permIds = DB::table('permissions')->whereIn('name', array_column($rows, 'name'))->pluck('id', 'name')->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        $viewRoles = ['tresorier_mission', 'president_mission', 'secretaire_executif_mission', 'admin_mission'];
        foreach ($viewRoles as $rn) {
            if (! isset($roleIds[$rn])) {
                continue;
            }
            DB::table('permission_role')->insert([
                'permission_id' => $permIds['parametres.ventilation_recettes.view'],
                'role_id' => $roleIds[$rn],
            ]);
        }

        $updateRoles = ['tresorier_mission', 'admin_mission'];
        foreach ($updateRoles as $rn) {
            if (! isset($roleIds[$rn])) {
                continue;
            }
            DB::table('permission_role')->insert([
                'permission_id' => $permIds['parametres.ventilation_recettes.update'],
                'role_id' => $roleIds[$rn],
            ]);
        }
    }

    public function down(): void
    {
        $names = ['parametres.ventilation_recettes.view', 'parametres.ventilation_recettes.update'];
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
