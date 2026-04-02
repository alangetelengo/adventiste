<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParametresDistrictTest extends TestCase
{
    use RefreshDatabase;

    public function test_tresorier_eglise_ne_peut_pas_acceder_aux_districts(): void
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

        $this->actingAs($user)->get(route('parametres.districts.index'))->assertForbidden();
    }

    public function test_admin_mission_peut_creer_et_lister_un_district(): void
    {
        $mission = Mission::query()->create(['nom' => 'Mission test', 'nom_court' => 'MT']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $this->actingAs($user)
            ->get(route('parametres.districts.index'))
            ->assertOk();

        $response = $this->actingAs($user)->post(route('parametres.districts.store'), [
            'nom' => 'District Nord',
        ]);

        $district = District::query()->where('nom', 'District Nord')->first();
        $this->assertNotNull($district);
        $response->assertRedirect(route('parametres.districts.edit', $district));
        $this->assertSame((int) $mission->id, (int) $district->mission_id);
    }

    public function test_lecteur_mission_peut_voir_mais_pas_creer_district(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $district = District::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'D1',
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('lecteur_mission'),
        ]);

        $this->actingAs($user)->get(route('parametres.districts.index'))->assertOk();
        $this->actingAs($user)->get(route('parametres.districts.show', $district))->assertOk();
        $this->actingAs($user)->get(route('parametres.districts.create'))->assertForbidden();
        $this->actingAs($user)->get(route('parametres.districts.edit', $district))->assertForbidden();
    }

    public function test_district_d_une_autre_mission_renvoie_404(): void
    {
        $m1 = Mission::query()->create(['nom' => 'A', 'nom_court' => 'A']);
        $m2 = Mission::query()->create(['nom' => 'B', 'nom_court' => 'B']);
        $district = District::query()->create(['mission_id' => $m1->id, 'nom' => 'X']);
        $user = User::factory()->create([
            'mission_id' => $m2->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $this->actingAs($user)->get(route('parametres.districts.show', $district))->assertNotFound();
    }
}
