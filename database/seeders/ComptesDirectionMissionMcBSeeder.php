<?php

namespace Database\Seeders;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Comptes de direction MCB (mot de passe : password).
 * Talangai : trésorier et secrétaire d’église. Mission : trésorier, secrétaire exécutif, président.
 */
class ComptesDirectionMissionMcBSeeder extends Seeder
{
    public function run(): void
    {
        $mission = Mission::query()->firstOrCreate(
            ['nom' => 'Mission du Congo Brazzaville'],
            ['nom_court' => 'MCB']
        );

        $talangai = EgliseLocale::query()->where('code_unique', 'MCB-TALANGAI')->first();
        if ($talangai === null) {
            $this->command?->warn('Église MCB-TALANGAI absente — seuls les comptes mission seront créés.');
        }

        $comptes = [];

        if ($talangai !== null) {
            $comptes[] = [
                'email' => 'tresorier.talangai@mcb.cg',
                'name' => 'Trésorier — TALANGAI',
                'role' => 'tresorier_eglise',
                'eglise_locale_id' => $talangai->id,
            ];
            $comptes[] = [
                'email' => 'secretaire.talangai@mcb.cg',
                'name' => 'Secrétaire d’église — TALANGAI',
                'role' => 'secretaire_eglise',
                'eglise_locale_id' => $talangai->id,
            ];
        }

        $comptes = array_merge($comptes, [
            [
                'email' => 'tresorier.mission@mcb.cg',
                'name' => 'Trésorier de mission — MCB',
                'role' => 'tresorier_mission',
                'eglise_locale_id' => null,
            ],
            [
                'email' => 'secretaire.executif@mcb.cg',
                'name' => 'Secrétaire exécutif de mission — MCB',
                'role' => 'secretaire_executif_mission',
                'eglise_locale_id' => null,
            ],
            [
                'email' => 'president.mission@mcb.cg',
                'name' => 'Président de mission — MCB',
                'role' => 'president_mission',
                'eglise_locale_id' => null,
            ],
        ]);

        foreach ($comptes as $c) {
            $roleId = Role::idFor($c['role']);
            if ($roleId === null) {
                $this->command?->error('Rôle manquant : '.$c['role'].' — exécuter les migrations.');

                continue;
            }

            $user = User::query()->firstOrNew(['email' => $c['email']]);
            $wasNew = ! $user->exists;
            $user->fill([
                'name' => $c['name'],
                'password' => Hash::make('password'),
                'mission_id' => $mission->id,
                'eglise_locale_id' => $c['eglise_locale_id'],
                'role_id' => $roleId,
            ]);
            if ($wasNew) {
                $user->identifiant_public = (string) Str::uuid();
            }
            $user->save();
        }
    }
}
