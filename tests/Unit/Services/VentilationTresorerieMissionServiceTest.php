<?php

namespace Tests\Unit\Services;

use App\Models\Mission;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\MissionTresorerieVentilationLigne;
use App\Services\Finances\VentilationTresorerieMissionService;
use Tests\TestCase;

class VentilationTresorerieMissionServiceTest extends TestCase
{
    public function test_peut_suggerer_montants_depuis_aggregat(): void
    {
        $mission = Mission::factory()->create();

        $service = new VentilationTresorerieMissionService();

        // Tester avec des données inexistantes (devrait retourner 0)
        $suggère = $service->suggererDepuisAggregat($mission->id, 2026, 3);

        $this->assertIsArray($suggère);
        $this->assertArrayHasKey('dimes_eglises', $suggère);
        $this->assertArrayHasKey('offrandes_mois', $suggère);
        $this->assertEquals(0.0, $suggère['dimes_eglises']);
        $this->assertEquals(0.0, $suggère['offrandes_mois']);
    }

    public function test_calcule_montants_par_ligne_sans_erreur(): void
    {
        $mission = Mission::factory()->create();

        $rapport = MissionTresorerieRapportMensuel::factory()
            ->for($mission)
            ->create([
                'dimes_eglises' => 100000,
                'autres_dimes' => 50000,
                'offrandes_mois' => 30000,
            ]);

        // Créer quelques lignes de ventilation
        MissionTresorerieVentilationLigne::factory()
            ->for($mission)
            ->create([
                'kind' => MissionTresorerieVentilationLigne::KIND_POURCENTAGE_DIMES,
                'pourcentage' => 50,
            ]);

        MissionTresorerieVentilationLigne::factory()
            ->for($mission)
            ->create([
                'kind' => MissionTresorerieVentilationLigne::KIND_POURCENTAGE_OFFRANDES,
                'pourcentage' => 25,
            ]);

        $service = new VentilationTresorerieMissionService();

        $montants = $service->calculerMontantsParLigne($rapport);

        $this->assertIsArray($montants);
        $this->assertGreater(0, count($montants));

        foreach ($montants as $ligneId => $montant) {
            $this->assertIsArray($montant);
            $this->assertArrayHasKey('mois', $montant);
            $this->assertArrayHasKey('cumule', $montant);
            $this->assertIsFloat($montant['mois']) or $this->assertIsInt($montant['mois']);
        }
    }

    public function test_applique_pourcentages_correctement(): void
    {
        $mission = Mission::factory()->create();

        $rapport = MissionTresorerieRapportMensuel::factory()
            ->for($mission)
            ->create([
                'dimes_eglises' => 100000,
                'autres_dimes' => 0,
                'offrandes_mois' => 40000,
            ]);

        $ligneDimes = MissionTresorerieVentilationLigne::factory()
            ->for($mission)
            ->create([
                'kind' => MissionTresorerieVentilationLigne::KIND_POURCENTAGE_DIMES,
                'pourcentage' => 20, // 20% des dîmes = 20000
                'code' => 'DIMES_20',
            ]);

        $service = new VentilationTresorerieMissionService();
        $montants = $service->calculerMontantsParLigne($rapport);

        $montantCalcule = $montants[$ligneDimes->id]['mois'] ?? 0;

        // Vérifier que 20% de 100000 = 20000
        $this->assertEquals(20000.0, $montantCalcule);
    }
}
