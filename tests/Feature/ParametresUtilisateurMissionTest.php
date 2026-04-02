<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParametresUtilisateurMissionTest extends TestCase
{
    use RefreshDatabase;

    private const MDP_TEST = 'Password123!';

    public function test_lecteur_mission_ne_peut_pas_acceder_aux_utilisateurs(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('lecteur_mission'),
        ]);

        $this->actingAs($user)->get(route('parametres.utilisateurs.index'))->assertForbidden();
    }

    public function test_admin_mission_peut_creer_un_tresorier(): void
    {
        $mission = Mission::query()->create(['nom' => 'Mission test', 'nom_court' => 'MT']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'Paroisse',
            'code_unique' => 'MT-P',
            'actif' => true,
        ]);
        $admin = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $response = $this->actingAs($admin)->post(route('parametres.utilisateurs.store'), [
            'name' => 'Trésorier test',
            'email' => 'tresorier-test@example.test',
            'password' => self::MDP_TEST,
            'password_confirmation' => self::MDP_TEST,
            'role_id' => $this->roleId('tresorier_eglise'),
            'eglise_locale_id' => (string) $eglise->id,
        ]);

        $nouveau = User::query()->where('email', 'tresorier-test@example.test')->first();
        $this->assertNotNull($nouveau);
        $response->assertRedirect(route('parametres.utilisateurs.edit', $nouveau));
        $this->assertTrue($nouveau->hasRole('tresorier_eglise'));
        $this->assertSame((int) $eglise->id, (int) $nouveau->eglise_locale_id);
        $this->assertTrue(Hash::check(self::MDP_TEST, $nouveau->password));
    }

    public function test_admin_mission_peut_creer_un_secretaire_eglise(): void
    {
        $mission = Mission::query()->create(['nom' => 'Mission test', 'nom_court' => 'MT']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'Paroisse',
            'code_unique' => 'MT-P2',
            'actif' => true,
        ]);
        $admin = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $response = $this->actingAs($admin)->post(route('parametres.utilisateurs.store'), [
            'name' => 'Secrétaire test',
            'email' => 'secretaire-test@example.test',
            'password' => self::MDP_TEST,
            'password_confirmation' => self::MDP_TEST,
            'role_id' => $this->roleId('secretaire_eglise'),
            'eglise_locale_id' => (string) $eglise->id,
        ]);

        $nouveau = User::query()->where('email', 'secretaire-test@example.test')->first();
        $this->assertNotNull($nouveau);
        $response->assertRedirect(route('parametres.utilisateurs.edit', $nouveau));
        $this->assertTrue($nouveau->hasRole('secretaire_eglise'));
        $this->assertSame((int) $eglise->id, (int) $nouveau->eglise_locale_id);
    }

    public function test_edition_utilisateur_autre_mission_renvoie_404(): void
    {
        $m1 = Mission::query()->create(['nom' => 'A', 'nom_court' => 'A']);
        $m2 = Mission::query()->create(['nom' => 'B', 'nom_court' => 'B']);
        $admin = User::factory()->create([
            'mission_id' => $m2->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);
        $cible = User::factory()->create([
            'mission_id' => $m1->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
            'email' => 'autre@mission.test',
        ]);

        $this->actingAs($admin)->get(route('parametres.utilisateurs.edit', $cible))->assertNotFound();
    }

    public function test_impossible_de_retirer_le_dernier_admin_mission(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $admin = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
            'email' => 'seul-admin@test.test',
        ]);
        User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('lecteur_mission'),
            'email' => 'lecteur@test.test',
        ]);

        $this->actingAs($admin)->put(route('parametres.utilisateurs.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role_id' => $this->roleId('lecteur_mission'),
            'eglise_locale_id' => '',
        ])->assertSessionHasErrors('role_id');
    }

    public function test_deux_admins_peuvent_changer_le_role_de_l_un(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $adminA = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
            'email' => 'admin-a@test.test',
        ]);
        User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
            'email' => 'admin-b@test.test',
        ]);

        $this->actingAs($adminA)->put(route('parametres.utilisateurs.update', $adminA), [
            'name' => $adminA->name,
            'email' => $adminA->email,
            'role_id' => $this->roleId('lecteur_mission'),
            'eglise_locale_id' => '',
        ])->assertRedirect(route('parametres.utilisateurs.edit', $adminA));

        $this->assertTrue($adminA->fresh()->hasRole('lecteur_mission'));
    }
}
