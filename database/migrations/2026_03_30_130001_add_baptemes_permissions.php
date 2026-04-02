<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = [
            ['name' => 'baptemes.view_any', 'label' => 'Baptêmes — Voir la liste', 'group' => 'baptemes'],
            ['name' => 'baptemes.create', 'label' => 'Baptêmes — Créer un enregistrement', 'group' => 'baptemes'],
            ['name' => 'baptemes.update', 'label' => 'Baptêmes — Modifier un enregistrement', 'group' => 'baptemes'],
            ['name' => 'baptemes.delete', 'label' => 'Baptêmes — Supprimer un enregistrement', 'group' => 'baptemes'],
            ['name' => 'baptemes.certificat', 'label' => 'Baptêmes — Voir/imprimer le certificat', 'group' => 'baptemes'],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('permissions')->where('name', $row['name'])->exists();
            if (! $exists) {
                DB::table('permissions')->insert(array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        $permIds = DB::table('permissions')->pluck('id', 'name')->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        $allBaptemePerms = [
            'baptemes.view_any',
            'baptemes.create',
            'baptemes.update',
            'baptemes.delete',
            'baptemes.certificat',
        ];

        foreach (['admin_mission', 'secretaire_eglise', 'secretaire_executif_mission'] as $roleName) {
            if (! isset($roleIds[$roleName])) {
                continue;
            }
            foreach ($allBaptemePerms as $permName) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permIds[$permName],
                    'role_id' => $roleIds[$roleName],
                ]);
            }
        }

        foreach (['lecteur_mission', 'tresorier_eglise', 'tresorier_mission', 'president_mission'] as $roleName) {
            if (! isset($roleIds[$roleName])) {
                continue;
            }

            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permIds['baptemes.view_any'],
                'role_id' => $roleIds[$roleName],
            ]);
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permIds['baptemes.certificat'],
                'role_id' => $roleIds[$roleName],
            ]);
        }
    }

    public function down(): void
    {
        $names = ['baptemes.view_any', 'baptemes.create', 'baptemes.update', 'baptemes.delete', 'baptemes.certificat'];
        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();
    }
};

