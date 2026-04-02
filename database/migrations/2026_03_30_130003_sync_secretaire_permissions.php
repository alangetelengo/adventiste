<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roles = DB::table('roles')
            ->whereIn('name', ['secretaire_eglise', 'secretaire'])
            ->pluck('id')
            ->all();

        if ($roles === []) {
            return;
        }

        $permIds = DB::table('permissions')->pluck('id', 'name')->all();

        $required = [
            'membres.view_any',
            'membres.create',
            'membres.update',
            'membres.delete',
            'baptemes.view_any',
            'baptemes.create',
            'baptemes.update',
            'baptemes.delete',
            'baptemes.certificat',
        ];

        foreach ($roles as $roleId) {
            foreach ($required as $permName) {
                if (! isset($permIds[$permName])) {
                    continue;
                }
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permIds[$permName],
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $roles = DB::table('roles')
            ->whereIn('name', ['secretaire_eglise', 'secretaire'])
            ->pluck('id');

        $permIds = DB::table('permissions')
            ->whereIn('name', [
                'membres.view_any',
                'membres.create',
                'membres.update',
                'membres.delete',
                'baptemes.view_any',
                'baptemes.create',
                'baptemes.update',
                'baptemes.delete',
                'baptemes.certificat',
            ])->pluck('id');

        DB::table('permission_role')
            ->whereIn('role_id', $roles)
            ->whereIn('permission_id', $permIds)
            ->delete();
    }
};

