<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\RecapSabbatEglise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecapSabbatListeAuthentifieTest extends TestCase
{
    use RefreshDatabase;

    public function test_invite_est_redirige_vers_connexion(): void
    {
        $response = $this->get(route('finances.recaps.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_tresorier_voit_les_recaps_de_son_eglise(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'E1',
            'code_unique' => 'E1',
            'actif' => true,
        ]);
        $autre = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'Paroisse hors périmètre test',
            'code_unique' => 'ZZ-HORS-PERIMETRE',
            'actif' => true,
        ]);

        $user = User::factory()->create([
            'email' => 't@t.local',
            'password' => Hash::make('password'),
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('tresorier_eglise'),
        ]);

        RecapSabbatEglise::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $eglise->id,
            'date_sabbat' => '2026-03-07',
            'annee' => 2026,
            'mois' => 3,
            'statut' => 'brouillon',
        ]);
        RecapSabbatEglise::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $autre->id,
            'date_sabbat' => '2026-03-14',
            'annee' => 2026,
            'mois' => 3,
            'statut' => 'brouillon',
        ]);

        $response = $this->actingAs($user)->get(route('finances.recaps.index'));

        $response->assertOk();
        $response->assertSee('E1', false);
        $response->assertDontSee('Paroisse hors périmètre test', false);
    }
}
