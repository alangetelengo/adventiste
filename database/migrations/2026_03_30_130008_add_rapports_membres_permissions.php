<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'secretariat.rapports_membres.view_any', 'label' => 'Secrétariat — Voir les rapports membres', 'group' => 'secretariat'],
            ['name' => 'secretariat.rapports_membres.create', 'label' => 'Secrétariat — Créer un rapport membres', 'group' => 'secretariat'],
            ['name' => 'secretariat.rapports_membres.update', 'label' => 'Secrétariat — Modifier un rapport membres', 'group' => 'secretariat'],
            ['name' => 'secretariat.rapports_membres.delete', 'label' => 'Secrétariat — Supprimer un rapport membres', 'group' => 'secretariat'],
            ['name' => 'secretariat.rapports_membres.soumettre', 'label' => 'Secrétariat — Soumettre un rapport membres à la mission', 'group' => 'secretariat'],
            ['name' => 'secretariat.rapports_membres.review', 'label' => 'Secrétariat — Valider/Rejeter les rapports membres', 'group' => 'secretariat'],
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

        $assignments = [
            'secretariat.rapports_membres.view_any' => ['admin_mission', 'president_mission', 'secretaire_executif_mission', 'lecteur_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.create' => ['admin_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.update' => ['admin_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.delete' => ['admin_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.soumettre' => ['admin_mission', 'secretaire_eglise', 'secretaire'],
            'secretariat.rapports_membres.review' => ['admin_mission', 'president_mission', 'secretaire_executif_mission'],
        ];

        foreach ($assignments as $permissionName => $roleNames) {
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
            'secretariat.rapports_membres.view_any',
            'secretariat.rapports_membres.create',
            'secretariat.rapports_membres.update',
            'secretariat.rapports_membres.delete',
            'secretariat.rapports_membres.soumettre',
            'secretariat.rapports_membres.review',
        ];

        $permIds = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        if ($permIds->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $permIds)->delete();
        }

        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
