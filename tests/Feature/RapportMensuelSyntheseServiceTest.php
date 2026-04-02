<?php

namespace Tests\Feature;

use App\Models\AggregatSyntheseMissionMensuelle;
use App\Models\EgliseLocale;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\LigneSyntheseMensuelleEglise;
use App\Models\Mission;
use App\Models\RapportMensuelEglise;
use App\Models\RecapSabbatEglise;
use App\Models\TypeRecetteMission;
use App\Services\Finances\RapportMensuelSyntheseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RapportMensuelSyntheseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_regenerer_remplit_rapport_mensuel_synthese_et_aggregat_mission(): void
    {
        $mission = Mission::query()->create(['nom' => 'Mission test', 'nom_court' => 'MT']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'district_id' => null,
            'nom' => 'Centre',
            'code_unique' => 'EGL-001',
            'actif' => true,
        ]);

        $recap = RecapSabbatEglise::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $eglise->id,
            'date_sabbat' => '2026-03-07',
            'annee' => 2026,
            'mois' => 3,
            'statut' => 'soumis',
        ]);

        $idDime = (int) TypeRecetteMission::query()->where('mission_id', $mission->id)->where('code', 'dime')->value('id');
        $idOff = (int) TypeRecetteMission::query()->where('mission_id', $mission->id)->where('code', 'offrande_cultuelle')->value('id');
        $idDon = (int) TypeRecetteMission::query()->where('mission_id', $mission->id)->where('code', 'don')->value('id');

        LigneDimeOffrandeRecap::query()->create([
            'recap_sabbat_eglise_id' => $recap->id,
            'type_recette_id' => $idDime,
            'membre_id' => null,
            'nom_visiteur' => null,
            'origine' => LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE,
            'type_revenu' => LigneDimeOffrandeRecap::TYPE_DIME,
            'dimes' => 10000,
            'offrandes' => 0,
            'ordre_ligne' => 1,
            'statut_ligne' => LigneDimeOffrandeRecap::STATUT_VERROUILLE,
        ]);

        LigneDimeOffrandeRecap::query()->create([
            'recap_sabbat_eglise_id' => $recap->id,
            'type_recette_id' => $idOff,
            'membre_id' => null,
            'nom_visiteur' => null,
            'origine' => LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE,
            'type_revenu' => LigneDimeOffrandeRecap::TYPE_OFFRANDE,
            'dimes' => 0,
            'offrandes' => 10000,
            'ordre_ligne' => 2,
            'statut_ligne' => LigneDimeOffrandeRecap::STATUT_VERROUILLE,
        ]);

        LigneDimeOffrandeRecap::query()->create([
            'recap_sabbat_eglise_id' => $recap->id,
            'type_recette_id' => $idDon,
            'membre_id' => null,
            'nom_visiteur' => null,
            'origine' => LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE,
            'type_revenu' => LigneDimeOffrandeRecap::TYPE_DON,
            'dimes' => 0,
            'offrandes' => 2000,
            'ordre_ligne' => 3,
            'statut_ligne' => LigneDimeOffrandeRecap::STATUT_VERROUILLE,
        ]);

        $service = new RapportMensuelSyntheseService;
        $rapport = $service->regenererPourEgliseEtMois($eglise, 2026, 3);

        $this->assertInstanceOf(RapportMensuelEglise::class, $rapport);
        $this->assertSame('10000.00', $rapport->fresh()->total_dimes_mois);

        $ligneSabbat = $rapport->lignesSabbat()->first();
        $this->assertNotNull($ligneSabbat);
        $this->assertSame('10000.00', $ligneSabbat->total_dimes);
        $this->assertSame('5000.00', $ligneSabbat->moitie_offrandes);
        $this->assertSame('15000.00', $ligneSabbat->total_transferer_mission);

        $syntheseJour = LigneSyntheseMensuelleEglise::query()
            ->where('rapport_mensuel_eglise_id', $rapport->id)
            ->where('indice_sabbat', 1)
            ->first();
        $this->assertNotNull($syntheseJour);
        $this->assertSame('10000.00', $syntheseJour->offrande_ecole_sabbat);
        $this->assertSame('5000.00', $syntheseJour->fonds_missionnaires);
        $this->assertSame('7000.00', $syntheseJour->budget_eglise_locale);
        $this->assertSame('2000.00', $syntheseJour->autres_offrandes);

        $syntheseMois = LigneSyntheseMensuelleEglise::query()
            ->where('rapport_mensuel_eglise_id', $rapport->id)
            ->where('indice_sabbat', 0)
            ->first();
        $this->assertNotNull($syntheseMois);
        $this->assertSame('22000.00', $syntheseMois->montant_total);

        $agg = AggregatSyntheseMissionMensuelle::query()
            ->where('mission_id', $mission->id)
            ->where('annee', 2026)
            ->where('mois', 3)
            ->first();
        $this->assertNotNull($agg);
        $this->assertSame('22000.00', $agg->montant_total);
    }
}
