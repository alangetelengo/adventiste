<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'secretariat.rapports_membres.view_any' => ['admin_mission', 'president_mission', 'secretaire_executif_mission', 'lecteur_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.create' => ['admin_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.update' => ['admin_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.delete' => ['admin_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.soumettre' => ['admin_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.review' => ['admin_mission', 'president_mission', 'secretaire_executif_mission'],
        ];

        $permIds = DB::table('permissions')
            ->whereIn('name', array_keys($permissions))
            ->pluck('id', 'name');

        $roleIds = DB::table('roles')
            ->whereIn('name', collect($permissions)->flatten()->unique()->values()->all())
            ->pluck('id', 'name');

        foreach ($permissions as $permissionName => $roles) {
            if (! isset($permIds[$permissionName])) {
                continue;
            }

            foreach ($roles as $roleName) {
                if (! isset($roleIds[$roleName])) {
                    continue;
                }

                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permIds[$permissionName],
                    'role_id' => $roleIds[$roleName],
                ]);
            }
        }
    }

    public function down(): void
    {
        // No-op: avoid removing permissions from potentially customized role mappings.
    }
};
