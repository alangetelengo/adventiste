<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            ['name' => 'parametres.groupes_mission.view_any', 'label' => 'Paramètres — Voir les groupes mission', 'group' => 'parametres'],
            ['name' => 'parametres.groupes_mission.create', 'label' => 'Paramètres — Créer un groupe mission', 'group' => 'parametres'],
            ['name' => 'parametres.groupes_mission.update', 'label' => 'Paramètres — Modifier un groupe mission', 'group' => 'parametres'],
            ['name' => 'parametres.groupes_mission.delete', 'label' => 'Paramètres — Supprimer un groupe mission', 'group' => 'parametres'],
        ];
        foreach ($rows as $row) {
            DB::table('permissions')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $permIds = DB::table('permissions')->whereIn('name', array_column($rows, 'name'))->pluck('id', 'name')->all();
        $adminId = DB::table('roles')->where('name', 'admin_mission')->value('id');
        $lecteurId = DB::table('roles')->where('name', 'lecteur_mission')->value('id');

        foreach (array_keys($permIds) as $name) {
            DB::table('permission_role')->insert([
                'permission_id' => $permIds[$name],
                'role_id' => $adminId,
            ]);
        }

        DB::table('permission_role')->insert([
            'permission_id' => $permIds['parametres.groupes_mission.view_any'],
            'role_id' => $lecteurId,
        ]);
    }

    public function down(): void
    {
        $names = [
            'parametres.groupes_mission.view_any',
            'parametres.groupes_mission.create',
            'parametres.groupes_mission.update',
            'parametres.groupes_mission.delete',
        ];
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
