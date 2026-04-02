<?php

namespace Database\Seeders;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DonneesDemoSeeder extends Seeder
{
    /**
     * Comptes de démonstration (mot de passe : password), rattachés à la mission MCB et à l’église BACONGO si le seeder des églises a été exécuté.
     */
    public function run(): void
    {
        $mission = Mission::query()->firstOrCreate(
            ['nom' => 'Mission du Congo Brazzaville'],
            ['nom_court' => 'MCB']
        );

        $eglise = EgliseLocale::query()->where('code_unique', 'MCB-BACONGO')->first()
            ?? EgliseLocale::query()->firstOrCreate(
                ['code_unique' => 'DEMO-001'],
                [
                    'mission_id' => $mission->id,
                    'district_id' => null,
                    'nom' => 'Église démo — Centre',
                    'actif' => true,
                ]
            );

        User::query()->updateOrCreate(
            ['email' => 'tresorier@demo.local'],
            [
                'name' => 'Trésorier démo',
                'password' => Hash::make('password'),
                'mission_id' => $mission->id,
                'eglise_locale_id' => $eglise->id,
                'role_id' => Role::idFor('tresorier_eglise'),
                'identifiant_public' => (string) Str::uuid(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'mission@demo.local'],
            [
                'name' => 'Lecteur mission démo',
                'password' => Hash::make('password'),
                'mission_id' => $mission->id,
                'eglise_locale_id' => null,
                'role_id' => Role::idFor('lecteur_mission'),
                'identifiant_public' => (string) Str::uuid(),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'admin-mission@demo.local'],
            [
                'name' => 'Admin mission démo',
                'password' => Hash::make('password'),
                'mission_id' => $mission->id,
                'eglise_locale_id' => null,
                'role_id' => Role::idFor('admin_mission'),
                'identifiant_public' => (string) Str::uuid(),
            ]
        );
    }
}
