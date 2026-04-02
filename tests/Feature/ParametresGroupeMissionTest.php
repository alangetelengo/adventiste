<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\GroupeMission;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParametresGroupeMissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_tresorier_eglise_ne_peut_pas_acceder_aux_groupes_mission(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'E',
            'code_unique' => 'E1',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('tresorier_eglise'),
        ]);

        $this->actingAs($user)->get(route('parametres.groupes-mission.index'))->assertForbidden();
    }

    public function test_admin_mission_peut_creer_et_lister_un_groupe(): void
    {
        $mission = Mission::query()->create(['nom' => 'Mission test', 'nom_court' => 'MT']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $this->actingAs($user)
            ->get(route('parametres.groupes-mission.index'))
            ->assertOk();

        $response = $this->actingAs($user)->post(route('parametres.groupes-mission.store'), [
            'nom' => 'Jeunesse',
            'code_unique' => 'MT-JEUNESSE',
            'actif' => '1',
        ]);

        $groupe = GroupeMission::query()->where('nom', 'Jeunesse')->first();
        $this->assertNotNull($groupe);
        $response->assertRedirect(route('parametres.groupes-mission.edit', $groupe));
        $this->assertSame((int) $mission->id, (int) $groupe->mission_id);
        $this->assertTrue($groupe->actif);
    }

    public function test_lecteur_mission_peut_voir_mais_pas_creer_groupe(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $groupe = GroupeMission::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'G1',
            'code_unique' => 'M-G1',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('lecteur_mission'),
        ]);

        $this->actingAs($user)->get(route('parametres.groupes-mission.index'))->assertOk();
        $this->actingAs($user)->get(route('parametres.groupes-mission.show', $groupe))->assertOk();
        $this->actingAs($user)->get(route('parametres.groupes-mission.create'))->assertForbidden();
        $this->actingAs($user)->get(route('parametres.groupes-mission.edit', $groupe))->assertForbidden();
    }

    public function test_groupe_d_une_autre_mission_renvoie_404(): void
    {
        $m1 = Mission::query()->create(['nom' => 'A', 'nom_court' => 'A']);
        $m2 = Mission::query()->create(['nom' => 'B', 'nom_court' => 'B']);
        $groupe = GroupeMission::query()->create([
            'mission_id' => $m1->id,
            'nom' => 'X',
            'code_unique' => 'A-X',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $m2->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $this->actingAs($user)->get(route('parametres.groupes-mission.show', $groupe))->assertNotFound();
    }
}
