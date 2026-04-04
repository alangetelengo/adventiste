<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\RapportMensuelEglise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RapportMensuelEgliseCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_tresorier_peut_creer_un_rapport_mensuel(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'E1',
            'code_unique' => 'E1',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('tresorier_eglise'),
        ]);

        $response = $this->actingAs($user)->post(route('finances.rapports-mensuels.store'), [
            'annee' => 2026,
            'mois' => 5,
        ]);

        $rapport = RapportMensuelEglise::query()->where('eglise_locale_id', $eglise->id)->first();
        $this->assertNotNull($rapport);
        $response->assertRedirect(route('finances.rapports-mensuels.show', $rapport));
    }

    public function test_periode_deja_presente_redirige_vers_la_fiche(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'E1',
            'code_unique' => 'E1',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('tresorier_eglise'),
        ]);

        RapportMensuelEglise::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $eglise->id,
            'annee' => 2026,
            'mois' => 4,
        ]);

        $response = $this->actingAs($user)->post(route('finances.rapports-mensuels.store'), [
            'annee' => 2026,
            'mois' => 4,
        ]);

        $rapport = RapportMensuelEglise::query()->where('eglise_locale_id', $eglise->id)->first();
        $response->assertRedirect(route('finances.rapports-mensuels.show', $rapport));
        $response->assertSessionHas('info');
    }

    public function test_utilisateur_mission_sans_eglise_ne_peut_pas_creer_rapport(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $this->actingAs($user)
            ->get(route('finances.rapports-mensuels.create'))
            ->assertForbidden();
    }

    public function test_utilisateur_mission_voit_les_rapports_des_eglises(): void
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
        RapportMensuelEglise::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $e1->id,
            'annee' => 2026,
            'mois' => 1,
        ]);
        RapportMensuelEglise::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $e2->id,
            'annee' => 2026,
            'mois' => 2,
        ]);

        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $response = $this->actingAs($user)->get(route('finances.rapports-mensuels.index'));

        $response->assertOk();
        $response->assertSee('E1', false);
        $response->assertSee('E2', false);
    }

    public function test_secretaire_eglise_ne_peut_pas_consulter_la_liste_rapports_mensuels(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'E1',
            'code_unique' => 'E1',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('secretaire_eglise'),
        ]);

        $this->actingAs($user)->get(route('finances.rapports-mensuels.index'))->assertForbidden();
    }
}
