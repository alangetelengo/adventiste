<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            ['name' => 'finances.recaps.soumettre', 'label' => 'Finances — Soumettre le récap à la mission', 'group' => 'finances'],
            ['name' => 'finances.recaps.valider_mission', 'label' => 'Finances — Accepter ou refuser un récap (mission)', 'group' => 'finances'],
            ['name' => 'parametres.types_recette.view', 'label' => 'Paramètres — Voir les types de recette', 'group' => 'parametres'],
            ['name' => 'parametres.types_recette.update', 'label' => 'Paramètres — Gérer les types de recette', 'group' => 'parametres'],
        ];
        foreach ($rows as $row) {
            DB::table('permissions')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $permIds = DB::table('permissions')->whereIn('name', array_column($rows, 'name'))->pluck('id', 'name')->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        foreach (['tresorier_mission', 'president_mission', 'secretaire_executif_mission', 'admin_mission'] as $rn) {
            if (! isset($roleIds[$rn])) {
                continue;
            }
            foreach (['parametres.types_recette.view', 'finances.recaps.valider_mission'] as $p) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permIds[$p],
                    'role_id' => $roleIds[$rn],
                ]);
            }
        }

        foreach (['tresorier_mission', 'admin_mission'] as $rn) {
            if (! isset($roleIds[$rn])) {
                continue;
            }
            DB::table('permission_role')->insert([
                'permission_id' => $permIds['parametres.types_recette.update'],
                'role_id' => $roleIds[$rn],
            ]);
        }

        foreach (['tresorier_eglise'] as $rn) {
            if (! isset($roleIds[$rn])) {
                continue;
            }
            DB::table('permission_role')->insert([
                'permission_id' => $permIds['finances.recaps.soumettre'],
                'role_id' => $roleIds[$rn],
            ]);
        }
    }

    public function down(): void
    {
        $names = [
            'finances.recaps.soumettre',
            'finances.recaps.valider_mission',
            'parametres.types_recette.view',
            'parametres.types_recette.update',
        ];
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
