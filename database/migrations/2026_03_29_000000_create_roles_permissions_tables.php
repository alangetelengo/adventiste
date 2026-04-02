<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->string('label');
            $table->string('group', 80)->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('label');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        $now = now();

        $permissionRows = [
            ['name' => 'finances.recaps.view_any', 'label' => 'Finances — Voir la liste des récaps', 'group' => 'finances'],
            ['name' => 'finances.recaps.create', 'label' => 'Finances — Créer un récap', 'group' => 'finances'],
            ['name' => 'finances.recaps.update', 'label' => 'Finances — Modifier un récap', 'group' => 'finances'],
            ['name' => 'finances.rapports.view_any', 'label' => 'Finances — Voir les rapports mensuels', 'group' => 'finances'],
            ['name' => 'finances.rapports.create', 'label' => 'Finances — Créer un rapport mensuel', 'group' => 'finances'],
            ['name' => 'finances.rapports.update', 'label' => 'Finances — Modifier un rapport (signatures)', 'group' => 'finances'],
            ['name' => 'finances.rapports.delete', 'label' => 'Finances — Supprimer un rapport mensuel', 'group' => 'finances'],
            ['name' => 'parametres.eglises.view_any', 'label' => 'Paramètres — Voir les églises', 'group' => 'parametres'],
            ['name' => 'parametres.eglises.create', 'label' => 'Paramètres — Créer une église', 'group' => 'parametres'],
            ['name' => 'parametres.eglises.update', 'label' => 'Paramètres — Modifier une église', 'group' => 'parametres'],
            ['name' => 'parametres.eglises.delete', 'label' => 'Paramètres — Supprimer une église', 'group' => 'parametres'],
            ['name' => 'parametres.districts.view_any', 'label' => 'Paramètres — Voir les districts', 'group' => 'parametres'],
            ['name' => 'parametres.districts.create', 'label' => 'Paramètres — Créer un district', 'group' => 'parametres'],
            ['name' => 'parametres.districts.update', 'label' => 'Paramètres — Modifier un district', 'group' => 'parametres'],
            ['name' => 'parametres.districts.delete', 'label' => 'Paramètres — Supprimer un district', 'group' => 'parametres'],
            ['name' => 'parametres.utilisateurs.manage', 'label' => 'Paramètres — Gérer les utilisateurs', 'group' => 'parametres'],
            ['name' => 'parametres.roles.manage', 'label' => 'Paramètres — Gérer les rôles', 'group' => 'parametres'],
            ['name' => 'parametres.permissions.manage', 'label' => 'Paramètres — Gérer les permissions', 'group' => 'parametres'],
        ];

        foreach ($permissionRows as $row) {
            DB::table('permissions')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $roleRows = [
            ['name' => 'admin_mission', 'label' => 'Administrateur mission', 'is_system' => true],
            ['name' => 'lecteur_mission', 'label' => 'Lecteur mission', 'is_system' => true],
            ['name' => 'tresorier_eglise', 'label' => 'Trésorier d’église', 'is_system' => true],
        ];

        foreach ($roleRows as $row) {
            DB::table('roles')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $permIds = DB::table('permissions')->pluck('id', 'name')->all();
        $roleIds = DB::table('roles')->pluck('id', 'name')->all();

        $allPermNames = array_keys($permIds);

        $lecteurPerms = [
            'finances.recaps.view_any',
            'finances.rapports.view_any',
            'parametres.eglises.view_any',
            'parametres.districts.view_any',
        ];

        $tresorierPerms = [
            'finances.recaps.view_any',
            'finances.recaps.create',
            'finances.recaps.update',
            'finances.rapports.view_any',
            'finances.rapports.create',
            'finances.rapports.update',
            'finances.rapports.delete',
        ];

        $pivotRows = [];
        foreach ($allPermNames as $pname) {
            $pivotRows[] = [
                'permission_id' => $permIds[$pname],
                'role_id' => $roleIds['admin_mission'],
            ];
        }
        foreach ($lecteurPerms as $pname) {
            $pivotRows[] = [
                'permission_id' => $permIds[$pname],
                'role_id' => $roleIds['lecteur_mission'],
            ];
        }
        foreach ($tresorierPerms as $pname) {
            $pivotRows[] = [
                'permission_id' => $permIds[$pname],
                'role_id' => $roleIds['tresorier_eglise'],
            ];
        }

        foreach ($pivotRows as $row) {
            DB::table('permission_role')->insert($row);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('eglise_locale_id')->constrained('roles')->restrictOnDelete();
        });

        $roleMap = DB::table('roles')->pluck('id', 'name')->all();

        foreach (DB::table('users')->select('id', 'role')->whereNotNull('role')->cursor() as $userRow) {
            if (isset($roleMap[$userRow->role])) {
                DB::table('users')->where('id', $userRow->id)->update(['role_id' => $roleMap[$userRow->role]]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 64)->nullable()->after('eglise_locale_id');
        });

        $roleMap = DB::table('roles')->pluck('name', 'id')->all();

        foreach (DB::table('users')->select('id', 'role_id')->whereNotNull('role_id')->cursor() as $userRow) {
            $name = $roleMap[$userRow->role_id] ?? null;
            if ($name !== null) {
                DB::table('users')->where('id', $userRow->id)->update(['role' => $name]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
