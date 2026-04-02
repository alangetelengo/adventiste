<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\RecapSabbatEglise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TableauDeBordTest extends TestCase
{
    use RefreshDatabase;

    public function test_tableau_de_bord_affiche_des_indicateurs_pour_admin_mission(): void
    {
        $mission = Mission::query()->create(['nom' => 'Mission test', 'nom_court' => 'MT']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'Paroisse A',
            'code_unique' => 'MT-A',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        RecapSabbatEglise::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $eglise->id,
            'date_sabbat' => now()->startOfMonth()->toDateString(),
            'annee' => (int) now()->year,
            'mois' => (int) now()->month,
            'statut' => 'brouillon',
        ]);

        $response = $this->actingAs($user)->get(route('tableau-de-bord'));

        $response->assertOk();
        $response->assertSee('Priorités du jour', false);
        $response->assertSee('Rapports églises en retard', false);
        $response->assertSee('Actions rapides', false);
        $response->assertSee('Administration complète', false);
        $response->assertSee('Rapports total', false);
    }
}
