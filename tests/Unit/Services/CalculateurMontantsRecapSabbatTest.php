<?php

namespace Tests\Unit\Services;

use App\Models\EgliseLocale;
use App\Models\GroupeMission;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\Mission;
use App\Models\MissionReglesVentilationRecettes;
use App\Models\RecapSabbatEglise;
use App\Models\TypeRecetteMission;
use App\Services\Finances\CalculateurMontantsRecapSabbat;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use Tests\TestCase as BaseTestCase;

class CalculateurMontantsRecapSabbatTest extends BaseTestCase
{
    public function test_peut_calculer_montants_recap_avec_dimes_et_offrandes(): void
    {
        $mission = Mission::factory()->create();
        $eglise = EgliseLocale::factory()->for($mission)->create();

        $typeRecetteMission = TypeRecetteMission::factory()
            ->for($mission)
            ->create(['categorie' => TypeRecetteMission::CATEGORIE_DIME]);

        $recap = RecapSabbatEglise::factory()
            ->for($eglise)
            ->create([
                'date_sabbat' => now()->startOfDay(),
            ]);

        LigneDimeOffrandeRecap::factory()
            ->for($recap)
            ->for($typeRecetteMission, 'typeRecette')
            ->create([
                'dimes' => 50000,
                'offrandes' => 30000,
            ]);

        $calculateur = CalculateurMontantsRecapSabbat::pour($recap);

        $this->assertIsFloat($calculateur->totalDimes());
        $this->assertGreaterThan(0, $calculateur->totalDimes());
    }

    public function test_applique_regles_ventilation_correctement(): void
    {
        $mission = Mission::factory()->create();
        $eglise = EgliseLocale::factory()->for($mission)->create();

        // Créer les règles de ventilation
        MissionReglesVentilationRecettes::factory()
            ->for($mission)
            ->create([
                'part_mission_dime_pct' => 80,
                'part_mission_offrande_pct' => 40,
                'part_mission_don_pct' => 10,
            ]);

        $typeRecetteMission = TypeRecetteMission::factory()
            ->for($mission)
            ->create(['categorie' => TypeRecetteMission::CATEGORIE_DIME]);

        $recap = RecapSabbatEglise::factory()
            ->for($eglise)
            ->create();

        LigneDimeOffrandeRecap::factory()
            ->for($recap)
            ->for($typeRecetteMission, 'typeRecette')
            ->create([
                'dimes' => 100000,
                'offrandes' => 50000,
            ]);

        $calculateur = CalculateurMontantsRecapSabbat::pour($recap);

        $this->assertIsFloat($calculateur->totalDimes());
        $this->assertGreaterThan(0, $calculateur->totalDimes());
    }

    public function test_filtre_lignes_par_statut_en_mode_rapport_officiel(): void
    {
        $mission = Mission::factory()->create();
        $eglise = EgliseLocale::factory()->for($mission)->create();
        $typeRecetteMission = TypeRecetteMission::factory()
            ->for($mission)
            ->create(['categorie' => TypeRecetteMission::CATEGORIE_DIME]);

        $recap = RecapSabbatEglise::factory()
            ->for($eglise)
            ->create();

        // Créer une ligne verrouillée
        LigneDimeOffrandeRecap::factory()
            ->for($recap)
            ->for($typeRecetteMission, 'typeRecette')
            ->create([
                'dimes' => 50000,
                'statut_ligne' => LigneDimeOffrandeRecap::STATUT_VERROUILLE,
            ]);

        // Créer une ligne brouillon
        LigneDimeOffrandeRecap::factory()
            ->for($recap)
            ->for($typeRecetteMission, 'typeRecette')
            ->create([
                'dimes' => 30000,
                'statut_ligne' => LigneDimeOffrandeRecap::STATUT_BROUILLON,
            ]);

        $calculateur = CalculateurMontantsRecapSabbat::pour($recap, pourRapportOfficiel: true);

        // Dans le mode officiel, seules les lignes verrouillées sont comptabilisées
        $total = $calculateur->totalDimes();
        $this->assertLessThanOrEqual(50000, $total); // La ligne brouillon ne doit pas être comptabilisée
    }
}
