<?php

namespace Tests\Feature;

use App\Models\AggregatSyntheseMissionMensuelle;
use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\MissionTresorerieVentilationLigne;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentilationTresorerieMissionTest extends TestCase
{
    use RefreshDatabase;

    private function seedDeuxLignesPourcentage(Mission $mission): void
    {
        $now = now();
        MissionTresorerieVentilationLigne::query()->insert([
            [
                'mission_id' => $mission->id,
                'code' => 'pct_d',
                'designation' => '10 % dîmes',
                'kind' => MissionTresorerieVentilationLigne::KIND_POURCENTAGE_DIMES,
                'pourcentage' => 10,
                'somme_codes' => null,
                'ordre' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'mission_id' => $mission->id,
                'code' => 'pct_o',
                'designation' => '25 % offrandes',
                'kind' => MissionTresorerieVentilationLigne::KIND_POURCENTAGE_OFFRANDES,
                'pourcentage' => 25,
                'somme_codes' => null,
                'ordre' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function test_tresorier_mission_peut_enregistrer_et_calcule_les_montants(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $this->seedDeuxLignesPourcentage($mission);

        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('tresorier_mission'),
        ]);

        $this->actingAs($user)->put(
            route('finances.ventilation-tresorerie-mission.update', ['annee' => 2026, 'mois' => 3]),
            [
                'dimes_eglises' => '1000',
                'autres_dimes' => '500',
                'offrandes_mois' => '800',
            ]
        )->assertRedirect(route('finances.ventilation-tresorerie-mission.edit', ['annee' => 2026, 'mois' => 3]));

        $rapport = MissionTresorerieRapportMensuel::query()
            ->where('mission_id', $mission->id)
            ->where('annee', 2026)
            ->where('mois', 3)
            ->first();

        $this->assertNotNull($rapport);
        $this->assertEquals(1500.0, $rapport->totalDimesMois());

        $ligneD = MissionTresorerieVentilationLigne::query()->where('mission_id', $mission->id)->where('code', 'pct_d')->first();
        $montantD = $rapport->ligneMontants()->where('ligne_id', $ligneD->id)->first();
        $this->assertNotNull($montantD);
        $this->assertEquals(150.0, (float) $montantD->montant_mois);

        $ligneO = MissionTresorerieVentilationLigne::query()->where('mission_id', $mission->id)->where('code', 'pct_o')->first();
        $montantO = $rapport->ligneMontants()->where('ligne_id', $ligneO->id)->first();
        $this->assertNotNull($montantO);
        $this->assertEquals(200.0, (float) $montantO->montant_mois);
    }

    public function test_cumul_mois_suivant_inclut_le_mois_precedent(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $this->seedDeuxLignesPourcentage($mission);

        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('tresorier_mission'),
        ]);

        $payload = [
            'dimes_eglises' => '1000',
            'autres_dimes' => '0',
            'offrandes_mois' => '0',
        ];

        $this->actingAs($user)->put(
            route('finances.ventilation-tresorerie-mission.update', ['annee' => 2026, 'mois' => 1]),
            $payload
        )->assertRedirect();

        $this->actingAs($user)->put(
            route('finances.ventilation-tresorerie-mission.update', ['annee' => 2026, 'mois' => 2]),
            $payload
        )->assertRedirect();

        $ligneD = MissionTresorerieVentilationLigne::query()->where('mission_id', $mission->id)->where('code', 'pct_d')->first();
        $rapport2 = MissionTresorerieRapportMensuel::query()
            ->where('mission_id', $mission->id)
            ->where('annee', 2026)
            ->where('mois', 2)
            ->first();

        $montantFev = $rapport2->ligneMontants()->where('ligne_id', $ligneD->id)->first();
        $this->assertNotNull($montantFev);
        $this->assertEquals(100.0, (float) $montantFev->montant_mois);
        $this->assertEquals(200.0, (float) $montantFev->montant_cumule);
    }

    public function test_utilisateur_eglise_ne_peut_pas_acceder(): void
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

        $this->actingAs($user)
            ->get(route('finances.ventilation-tresorerie-mission.index'))
            ->assertForbidden();
    }

    public function test_president_peut_voir_mais_pas_enregistrer(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $this->seedDeuxLignesPourcentage($mission);

        $president = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('president_mission'),
        ]);

        $this->actingAs($president)
            ->get(route('finances.ventilation-tresorerie-mission.edit', ['annee' => 2026, 'mois' => 1]))
            ->assertOk();

        $this->actingAs($president)
            ->put(route('finances.ventilation-tresorerie-mission.update', ['annee' => 2026, 'mois' => 1]), [
                'dimes_eglises' => '10',
                'autres_dimes' => '0',
                'offrandes_mois' => '0',
            ])
            ->assertForbidden();
    }

    public function test_pre_remplissage_depuis_aggregat(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $this->seedDeuxLignesPourcentage($mission);

        AggregatSyntheseMissionMensuelle::query()->create([
            'mission_id' => $mission->id,
            'annee' => 2026,
            'mois' => 5,
            'dimes' => 1234.56,
            'offrande_ecole_sabbat' => 789.12,
            'budget_eglise_locale' => 0,
            'fonds_missionnaires' => 0,
            'autres_offrandes' => 0,
            'montant_total' => 0,
            'calcule_le' => now(),
        ]);

        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('tresorier_mission'),
        ]);

        $html = $this->actingAs($user)
            ->get(route('finances.ventilation-tresorerie-mission.edit', ['annee' => 2026, 'mois' => 5, 'suggere' => 1]))
            ->assertOk()
            ->getContent();

        $this->assertTrue(
            str_contains($html, '1234.56') || str_contains($html, '1234,56'),
            'La page doit afficher les montants issus de l’agrégat.'
        );
    }
}
