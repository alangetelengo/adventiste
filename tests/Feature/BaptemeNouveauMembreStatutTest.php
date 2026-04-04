<?php

namespace Tests\Feature;

use App\Models\Bapteme;
use App\Models\EgliseLocale;
use App\Models\Membre;
use App\Models\MembreHistoriqueStatut;
use App\Models\Mission;
use App\Models\TypeStatutMembre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaptemeNouveauMembreStatutTest extends TestCase
{
    use RefreshDatabase;

    public function test_nouveau_baptise_recoit_statut_regulier_si_defini_pour_la_mission(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'Paroisse',
            'code_unique' => 'P1',
            'actif' => true,
        ]);
        $regulier = TypeStatutMembre::query()->create([
            'mission_id' => $mission->id,
            'code' => TypeStatutMembre::CODE_REGULIER,
            'libelle' => 'Régulier',
            'description' => null,
            'couleur' => '#2563eb',
            'ordre' => 20,
            'actif' => true,
            'is_system' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('secretaire_eglise'),
        ]);

        $this->actingAs($user)->post(route('baptemes.store'), [
            'eglise_locale_id' => $eglise->id,
            'nom' => 'TESTBAPT',
            'prenom' => 'Nouveau',
            'type_bapteme' => Bapteme::TYPE_IMMERSION,
            'date_bapteme' => '2026-04-10',
        ])->assertRedirect();

        $membre = Membre::query()->where('nom', 'TESTBAPT')->where('prenom', 'Nouveau')->first();
        $this->assertNotNull($membre);
        $this->assertSame((int) $regulier->id, (int) $membre->type_statut_membre_id);
        $this->assertTrue($membre->actif);

        $this->assertTrue(
            MembreHistoriqueStatut::query()
                ->where('membre_id', $membre->id)
                ->where('type_statut_membre_id', $regulier->id)
                ->exists()
        );
    }

    public function test_nouveau_baptise_recoit_actif_si_regulier_absent(): void
    {
        $mission = Mission::query()->create(['nom' => 'M2', 'nom_court' => 'M2']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'Centre',
            'code_unique' => 'C1',
            'actif' => true,
        ]);
        $actif = TypeStatutMembre::query()->create([
            'mission_id' => $mission->id,
            'code' => TypeStatutMembre::CODE_ACTIF,
            'libelle' => 'Actif',
            'description' => null,
            'couleur' => '#16a34a',
            'ordre' => 10,
            'actif' => true,
            'is_system' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('secretaire_eglise'),
        ]);

        $this->actingAs($user)->post(route('baptemes.store'), [
            'eglise_locale_id' => $eglise->id,
            'nom' => 'FALLBACK',
            'prenom' => 'Bapt',
            'type_bapteme' => Bapteme::TYPE_PROFESSION_FOI,
            'date_bapteme' => '2026-05-01',
        ])->assertRedirect();

        $membre = Membre::query()->where('nom', 'FALLBACK')->first();
        $this->assertNotNull($membre);
        $this->assertSame((int) $actif->id, (int) $membre->type_statut_membre_id);
    }
}
