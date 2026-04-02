<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = [
            ['name' => 'membres.view_any', 'label' => 'Membres — Voir la liste', 'group' => 'membres'],
            ['name' => 'membres.create', 'label' => 'Membres — Créer une fiche', 'group' => 'membres'],
            ['name' => 'membres.update', 'label' => 'Membres — Modifier une fiche', 'group' => 'membres'],
            ['name' => 'membres.delete', 'label' => 'Membres — Supprimer une fiche', 'group' => 'membres'],
        ];

        foreach ($rows as $row) {
            DB::table('permissions')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $permIds = DB::table('permissions')->pluck('id', 'name')->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        $adminId = $roleIds['admin_mission'];
        $lecteurId = $roleIds['lecteur_mission'];
        $tresorierId = $roleIds['tresorier_eglise'];

        foreach (['membres.view_any', 'membres.create', 'membres.update', 'membres.delete'] as $name) {
            DB::table('permission_role')->insert([
                'permission_id' => $permIds[$name],
                'role_id' => $adminId,
            ]);
        }

        DB::table('permission_role')->insert([
            'permission_id' => $permIds['membres.view_any'],
            'role_id' => $lecteurId,
        ]);

        foreach (['membres.view_any', 'membres.create', 'membres.update', 'membres.delete'] as $name) {
            DB::table('permission_role')->insert([
                'permission_id' => $permIds[$name],
                'role_id' => $tresorierId,
            ]);
        }
    }

    public function down(): void
    {
        $names = ['membres.view_any', 'membres.create', 'membres.update', 'membres.delete'];
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
