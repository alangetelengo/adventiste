<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $permissionRows = [
            ['name' => 'parametres.departements.view_any', 'label' => 'Paramètres — Voir les départements', 'group' => 'parametres'],
            ['name' => 'parametres.departements.create', 'label' => 'Paramètres — Créer un département', 'group' => 'parametres'],
            ['name' => 'parametres.departements.update', 'label' => 'Paramètres — Modifier un département', 'group' => 'parametres'],
            ['name' => 'parametres.departements.delete', 'label' => 'Paramètres — Supprimer un département', 'group' => 'parametres'],
        ];

        foreach ($permissionRows as $row) {
            DB::table('permissions')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // Create the secretaire_eglise role if it doesn't exist
        if (!DB::table('roles')->where('name', 'secretaire_eglise')->exists()) {
            DB::table('roles')->insert([
                'name' => 'secretaire_eglise',
                'label' => 'Secrétaire d\'église',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permIds = DB::table('permissions')->pluck('id', 'name')->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        $secretairePerms = [
            'parametres.departements.view_any',
            'parametres.departements.create',
            'parametres.departements.update',
            'parametres.departements.delete',
        ];

        $pivotRows = [];
        foreach ($secretairePerms as $pname) {
            $pivotRows[] = [
                'permission_id' => $permIds[$pname],
                'role_id' => $roleIds['secretaire_eglise'],
            ];
        }

        foreach ($pivotRows as $row) {
            DB::table('permission_role')->insert($row);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permNames = [
            'parametres.departements.view_any',
            'parametres.departements.create',
            'parametres.departements.update',
            'parametres.departements.delete',
        ];

        DB::table('permission_role')->whereIn('permission_id', function ($query) use ($permNames) {
            $query->select('id')->from('permissions')->whereIn('name', $permNames);
        })->delete();

        DB::table('permissions')->whereIn('name', $permNames)->delete();

        // Delete the role if it exists
        DB::table('roles')->where('name', 'secretaire_eglise')->delete();
    }
};
