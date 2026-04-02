<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissions = [
            ['name' => 'finances.rapports.soumettre', 'label' => 'Finances — Soumettre un rapport mensuel à la mission', 'group' => 'finances'],
            ['name' => 'finances.rapports.review_mission', 'label' => 'Finances — Valider/Refuser un rapport mensuel côté mission', 'group' => 'finances'],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm['name']],
                ['label' => $perm['label'], 'group' => $perm['group'], 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $permIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id', 'name');

        $assignments = [
            'finances.rapports.soumettre' => ['admin_mission', 'tresorier_eglise'],
            'finances.rapports.review_mission' => ['admin_mission', 'tresorier_mission', 'president_mission'],
        ];

        foreach ($assignments as $permName => $roleNames) {
            if (! isset($permIds[$permName])) {
                continue;
            }
            $roleIds = DB::table('roles')->whereIn('name', $roleNames)->pluck('id');
            foreach ($roleIds as $roleId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permIds[$permName],
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $names = ['finances.rapports.soumettre', 'finances.rapports.review_mission'];
        $permIds = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        if ($permIds->isNotEmpty()) {
            DB::table('permission_role')->whereIn('permission_id', $permIds)->delete();
        }
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};
