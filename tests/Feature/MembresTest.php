<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\Membre;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MembresTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecteur_mission_peut_consulter_la_liste(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        EgliseLocale::query()->create([
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

        $this->actingAs($user)->get(route('membres.index'))->assertOk();
    }

    public function test_lecteur_mission_ne_peut_pas_acceder_au_formulaire_creation(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('lecteur_mission'),
        ]);

        $this->actingAs($user)->get(route('membres.create'))->assertForbidden();
    }

    public function test_admin_mission_peut_creer_un_membre(): void
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

        $response = $this->actingAs($admin)->post(route('membres.store'), [
            'eglise_locale_id' => $eglise->id,
            'groupe_mission_id' => '',
            'mode_entree' => \App\Models\Membre::MODE_ENTREE_TRANSFERT,
            'nom' => 'Kimbembe',
            'prenom' => 'Paul',
            'telephone' => '+242060000000',
            'recu_dans_eglise_de' => 'Eglise Source',
            'recu_le' => now()->toDateString(),
        ]);

        $membre = Membre::query()->where('nom', 'Kimbembe')->where('prenom', 'Paul')->first();
        $this->assertNotNull($membre);
        $response->assertRedirect(route('membres.edit', $membre));
        $this->assertSame((int) $eglise->id, (int) $membre->eglise_locale_id);
        $this->assertNotEmpty($membre->identifiant_public);
    }

    public function test_tresorier_recupere_404_pour_membre_d_une_autre_eglise(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $e1 = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'E1',
            'code_unique' => 'E1',
            'actif' => true,
        ]);
        $e2 = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'E2',
            'code_unique' => 'E2',
            'actif' => true,
        ]);
        $membre = Membre::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $e2->id,
            'nom' => 'X',
            'prenom' => 'Y',
        ]);
        $tresorier = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $e1->id,
            'role_id' => $this->roleId('tresorier_eglise'),
        ]);

        $this->actingAs($tresorier)->get(route('membres.show', $membre))->assertNotFound();
    }

    public function test_tableau_de_bord_tresorier_propose_l_annuaire_membres(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'Centre',
            'code_unique' => 'C1',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('tresorier_eglise'),
        ]);

        $response = $this->actingAs($user)->get(route('tableau-de-bord'));

        $response->assertOk();
        $response->assertSee('Récaps saisis', false);
    }
}
