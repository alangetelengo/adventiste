<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParametresEgliseLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_tresorier_eglise_ne_peut_pas_acceder_au_crud_eglises(): void
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

        $this->actingAs($user)->get(route('parametres.eglises.index'))->assertForbidden();
    }

    public function test_admin_mission_peut_creer_et_lister_une_eglise(): void
    {
        $mission = Mission::query()->create(['nom' => 'Mission test', 'nom_court' => 'MT']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $this->actingAs($user)
            ->get(route('parametres.eglises.index'))
            ->assertOk();

        $response = $this->actingAs($user)->post(route('parametres.eglises.store'), [
            'nom' => 'Nouvelle paroisse',
            'code_unique' => 'MT-TEST-01',
            'actif' => '1',
            'district_id' => '',
        ]);

        $eglise = EgliseLocale::query()->where('code_unique', 'MT-TEST-01')->first();
        $this->assertNotNull($eglise);
        $response->assertRedirect(route('parametres.eglises.edit', $eglise));
        $this->assertSame((int) $mission->id, (int) $eglise->mission_id);
    }

    public function test_lecteur_mission_peut_voir_mais_pas_modifier(): void
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
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('lecteur_mission'),
        ]);

        $this->actingAs($user)->get(route('parametres.eglises.show', $eglise))->assertOk();
        $this->actingAs($user)->get(route('parametres.eglises.edit', $eglise))->assertForbidden();
    }
}
