<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permId = DB::table('permissions')->where('name', 'membres.create')->value('id');
        if ($permId === null) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['tresorier_eglise', 'tresorier_mission'])
            ->pluck('id')
            ->all();

        if ($roleIds === []) {
            return;
        }

        DB::table('permission_role')
            ->where('permission_id', $permId)
            ->whereIn('role_id', $roleIds)
            ->delete();
    }

    public function down(): void
    {
        $permId = DB::table('permissions')->where('name', 'membres.create')->value('id');
        if ($permId === null) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['tresorier_eglise', 'tresorier_mission'])
            ->pluck('id')
            ->all();

        foreach ($roleIds as $roleId) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permId,
                'role_id' => $roleId,
            ]);
        }
    }
};

