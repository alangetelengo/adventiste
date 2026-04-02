<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\Mission;
use App\Models\MissionReglesVentilationRecettes;
use App\Models\TypeRecetteMission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionVentilationRecettesTest extends TestCase
{
    use RefreshDatabase;

    public function test_tresorier_mission_peut_voir_et_modifier_la_ventilation(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('tresorier_mission'),
        ]);

        $this->actingAs($user)
            ->get(route('parametres.ventilation-recettes.edit'))
            ->assertOk()
            ->assertSee('Ventilation des types de recettes');

        $this->actingAs($user)
            ->put(route('parametres.ventilation-recettes.update'), [
                'part_mission_dime_pct' => 100,
                'part_mission_offrande_pct' => 40,
                'part_mission_don_pct' => 0,
                'libelle_rapport_dime' => 'Dîme biblique',
                'libelle_rapport_offrande' => null,
                'libelle_rapport_don' => null,
                'notes_internes' => null,
            ])
            ->assertRedirect(route('parametres.ventilation-recettes.edit'));

        $this->assertDatabaseHas('mission_regles_ventilation_recettes', [
            'mission_id' => $mission->id,
            'part_mission_offrande_pct' => 40,
        ]);
    }

    public function test_tresorier_eglise_ne_peut_pas_acceder_a_la_configuration_mission(): void
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
            ->get(route('parametres.ventilation-recettes.edit'))
            ->assertForbidden();
    }

    public function test_calculateur_utilise_les_pourcentages_mission(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        MissionReglesVentilationRecettes::query()->create([
            'mission_id' => $mission->id,
            'part_mission_dime_pct' => 100,
            'part_mission_offrande_pct' => 60,
            'part_mission_don_pct' => 0,
        ]);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'E',
            'code_unique' => 'E1',
            'actif' => true,
        ]);

        $recap = \App\Models\RecapSabbatEglise::query()->create([
            'identifiant_public' => (string) \Illuminate\Support\Str::uuid(),
            'eglise_locale_id' => $eglise->id,
            'date_sabbat' => '2026-08-01',
            'annee' => 2026,
            'mois' => 8,
            'statut' => 'brouillon',
        ]);

        $idOff = (int) TypeRecetteMission::query()->where('mission_id', $mission->id)->where('code', 'offrande_cultuelle')->value('id');

        \App\Models\LigneDimeOffrandeRecap::query()->create([
            'recap_sabbat_eglise_id' => $recap->id,
            'type_recette_id' => $idOff,
            'membre_id' => null,
            'nom_visiteur' => null,
            'origine' => \App\Models\LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE,
            'type_revenu' => \App\Models\LigneDimeOffrandeRecap::TYPE_OFFRANDE,
            'dimes' => 0,
            'offrandes' => 1000,
            'ordre_ligne' => 1,
            'statut_ligne' => \App\Models\LigneDimeOffrandeRecap::STATUT_BROUILLON,
        ]);

        $recap->load(['egliseLocale.mission.reglesVentilationRecettes', 'lignesContributions']);
        $calc = \App\Services\Finances\CalculateurMontantsRecapSabbat::pour($recap);

        $this->assertSame(600.0, $calc->moitiePourMissionSurOffrandes());
        $this->assertSame(400.0, $calc->partBudgetEgliseLocale());
    }
}
