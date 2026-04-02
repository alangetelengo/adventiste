<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $roleRows = [
            ['name' => 'secretaire_eglise', 'label' => 'Secrétaire d’église', 'is_system' => true],
            ['name' => 'tresorier_mission', 'label' => 'Trésorier de mission', 'is_system' => true],
            ['name' => 'secretaire_executif_mission', 'label' => 'Secrétaire exécutif de mission', 'is_system' => true],
            ['name' => 'president_mission', 'label' => 'Président de mission', 'is_system' => true],
        ];

        foreach ($roleRows as $row) {
            if (!DB::table('roles')->where('name', $row['name'])->exists()) {
                DB::table('roles')->insert(array_merge($row, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        $permIds = DB::table('permissions')->pluck('id', 'name')->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        $lecteurPerms = [
            'finances.recaps.view_any',
            'finances.rapports.view_any',
            'parametres.eglises.view_any',
            'parametres.districts.view_any',
            'membres.view_any',
            'parametres.groupes_mission.view_any',
        ];

        $membresWrite = ['membres.create', 'membres.update', 'membres.delete'];

        $pivot = [];

        foreach ($lecteurPerms as $name) {
            if (! isset($permIds[$name])) {
                continue;
            }
            $pivot[] = ['permission_id' => $permIds[$name], 'role_id' => $roleIds['secretaire_eglise']];
            $pivot[] = ['permission_id' => $permIds[$name], 'role_id' => $roleIds['tresorier_mission']];
            $pivot[] = ['permission_id' => $permIds[$name], 'role_id' => $roleIds['secretaire_executif_mission']];
            $pivot[] = ['permission_id' => $permIds[$name], 'role_id' => $roleIds['president_mission']];
        }

        foreach ($membresWrite as $name) {
            if (! isset($permIds[$name])) {
                continue;
            }
            $pivot[] = ['permission_id' => $permIds[$name], 'role_id' => $roleIds['secretaire_eglise']];
            $pivot[] = ['permission_id' => $permIds[$name], 'role_id' => $roleIds['secretaire_executif_mission']];
        }

        foreach ($pivot as $row) {
            DB::table('permission_role')->insertOrIgnore($row);
        }
    }

    public function down(): void
    {
        $names = ['secretaire_eglise', 'tresorier_mission', 'secretaire_executif_mission', 'president_mission'];
        $roleIds = DB::table('roles')->whereIn('name', $names)->pluck('id');

        DB::table('permission_role')->whereIn('role_id', $roleIds)->delete();
        DB::table('roles')->whereIn('name', $names)->delete();
    }
};
