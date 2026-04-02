<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'parametres.types_statut_membre.view', 'label' => 'Voir les types de statut membre', 'group' => 'parametres'],
            ['name' => 'parametres.types_statut_membre.update', 'label' => 'Gérer les types de statut membre', 'group' => 'parametres'],
            ['name' => 'membres.change_statut', 'label' => 'Changer le statut d\'un membre', 'group' => 'membres'],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm['name']],
                [
                    'label' => $perm['label'],
                    'group' => $perm['group'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $permIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id', 'name');

        $roleNamesByPermission = [
            'parametres.types_statut_membre.view' => ['admin_mission', 'president_mission', 'secretaire_executif_mission', 'lecteur_mission'],
            'parametres.types_statut_membre.update' => ['admin_mission', 'president_mission', 'secretaire_executif_mission'],
            'membres.change_statut' => ['admin_mission', 'president_mission', 'secretaire_executif_mission', 'secretaire_eglise', 'secretaire'],
        ];

        foreach ($roleNamesByPermission as $permissionName => $roleNames) {
            if (! isset($permIds[$permissionName])) {
                continue;
            }

            $roles = DB::table('roles')->whereIn('name', $roleNames)->pluck('id');
            foreach ($roles as $roleId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permIds[$permissionName],
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $names = [
            'parametres.types_statut_membre.view',
            'parametres.types_statut_membre.update',
            'membres.change_statut',
        ];

        $permIds = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        if ($permIds->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $permIds)->delete();
        }

        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
