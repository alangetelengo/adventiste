<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = [
            [
                'name' => 'finances.ventilation_tresorerie_mission.soumettre',
                'label' => 'Finances — Soumettre le rapport ventilation trésorerie mission',
                'group' => 'finances',
            ],
            [
                'name' => 'finances.ventilation_tresorerie_mission.review_mission',
                'label' => 'Finances — Valider/Refuser le rapport ventilation trésorerie mission',
                'group' => 'finances',
            ],
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')->where('name', $permission['name'])->exists();
            if ($exists) {
                DB::table('permissions')
                    ->where('name', $permission['name'])
                    ->update([
                        'label' => $permission['label'],
                        'group' => $permission['group'],
                        'updated_at' => $now,
                    ]);
                continue;
            }

            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'label' => $permission['label'],
                'group' => $permission['group'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id', 'name')
            ->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        foreach (['tresorier_mission', 'admin_mission'] as $roleName) {
            if (! isset($roleIds[$roleName]) || ! isset($permIds['finances.ventilation_tresorerie_mission.soumettre'])) {
                continue;
            }
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permIds['finances.ventilation_tresorerie_mission.soumettre'],
                'role_id' => $roleIds[$roleName],
            ]);
        }

        foreach (['president_mission', 'admin_mission'] as $roleName) {
            if (! isset($roleIds[$roleName]) || ! isset($permIds['finances.ventilation_tresorerie_mission.review_mission'])) {
                continue;
            }
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permIds['finances.ventilation_tresorerie_mission.review_mission'],
                'role_id' => $roleIds[$roleName],
            ]);
        }
    }

    public function down(): void
    {
        $names = [
            'finances.ventilation_tresorerie_mission.soumettre',
            'finances.ventilation_tresorerie_mission.review_mission',
        ];

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
