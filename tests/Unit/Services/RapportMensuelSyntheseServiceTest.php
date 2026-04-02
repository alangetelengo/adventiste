<?php

namespace Tests\Unit\Services;

use App\Models\EgliseLocale;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\Mission;
use App\Models\RecapSabbatEglise;
use App\Models\TypeRecetteMission;
use App\Services\Finances\RapportMensuelSyntheseService;
use Tests\TestCase;

class RapportMensuelSyntheseServiceTest extends TestCase
{
    public function test_peut_regenerer_rapport_pour_eglise_et_mois(): void
    {
        $mission = Mission::factory()->create();
        $eglise = EgliseLocale::factory()->for($mission)->create();

        $service = new RapportMensuelSyntheseService();

        $rapport = $service->regenererPourEgliseEtMois($eglise, 2026, 3);

        $this->assertNotNull($rapport);
        $this->assertEquals($eglise->id, $rapport->eglise_locale_id);
        $this->assertEquals(2026, $rapport->annee);
        $this->assertEquals(3, $rapport->mois);
    }

    public function test_regenerer_ne_modifie_pas_rapport_verrouille(): void
    {
        $mission = Mission::factory()->create();
        $eglise = EgliseLocale::factory()->for($mission)->create();

        // Créer un rapport verrouillé
        $rapport = $eglise->rapportsMensuels()->create([
            'annee' => 2026,
            'mois' => 3,
            'verrouille_le' => now(),
        ]);

        $service = new RapportMensuelSyntheseService();

        $rapportRegene = $service->regenererPourEgliseEtMois($eglise, 2026, 3);

        // Le rapport ne devrait pas être modifié s'il est verrouillé
        $this->assertNotNull($rapportRegene->verrouille_le);
    }

    public function test_cree_lignes_sabbat_a_partir_recaps(): void
    {
        $mission = Mission::factory()->create();
        $eglise = EgliseLocale::factory()->for($mission)->create();

        $typeRecetteMission = TypeRecetteMission::factory()
            ->for($mission)
            ->create(['categorie' => TypeRecetteMission::CATEGORIE_DIME]);

        // Créer plusieurs recaps pour le mois
        $now = now();
        RecapSabbatEglise::factory()
            ->for($eglise)
            ->create([
                'date_sabbat' => $now->startOfMonth(),
                'annee' => $now->year,
                'mois' => $now->month,
            ]);

        RecapSabbatEglise::factory()
            ->for($eglise)
            ->create([
                'date_sabbat' => $now->addDays(7)->startOfDay(),
                'annee' => $now->year,
                'mois' => $now->month,
            ]);

        $service = new RapportMensuelSyntheseService();
        $rapport = $service->regenererPourEgliseEtMois($eglise, $now->year, $now->month);

        $this->assertGreater(0, $rapport->lignesSabbat()->count());
    }

    public function test_aggrege_montants_mission(): void
    {
        $mission = Mission::factory()->create();
        $eglise = EgliseLocale::factory()->for($mission)->create();

        $now = now();
        RecapSabbatEglise::factory()
            ->for($eglise)
            ->create([
                'date_sabbat' => $now->startOfMonth(),
                'annee' => $now->year,
                'mois' => $now->month,
            ]);

        $service = new RapportMensuelSyntheseService();
        $rapport = $service->regenererPourEgliseEtMois($eglise, $now->year, $now->month);

        // Vérifier que l'agrégat mission a été créé/mis à jour
        $aggregats = $rapport->egliseLocale->mission->aggregatsSynthese()
            ->where('annee', $now->year)
            ->where('mois', $now->month)
            ->first();

        // L'agrégat devrait exister après la régénération
        $this->assertNotNull($aggregats);
    }
}
