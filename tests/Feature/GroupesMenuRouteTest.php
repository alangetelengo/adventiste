<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupesMenuRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_utilisateur_mission_avec_droit_voit_la_redirection_vers_groupes_mission(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('lecteur_mission'),
        ]);

        $this->actingAs($user)
            ->get(route('groupes'))
            ->assertRedirect(route('parametres.groupes-mission.index'));
    }

    public function test_tresorier_obtient_la_page_statique_groupes(): void
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

        $response = $this->actingAs($user)->get(route('groupes'));

        $response->assertOk();
        $response->assertSee('à venir', false);
    }
}
