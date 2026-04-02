<?php

namespace Tests\Feature;

use App\Models\EgliseLocale;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\Membre;
use App\Models\Mission;
use App\Models\RecapSabbatEglise;
use App\Models\TypeRecetteMission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecapSabbatCrudTest extends TestCase
{
    use RefreshDatabase;

    private function utilisateurTresorier(): array
    {
        $mission = Mission::query()->create(['nom' => 'Mission', 'nom_court' => 'M']);
        $eglise = EgliseLocale::query()->create([
            'mission_id' => $mission->id,
            'nom' => 'Église test',
            'code_unique' => 'ET',
            'actif' => true,
        ]);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => $eglise->id,
            'role_id' => $this->roleId('tresorier_eglise'),
        ]);

        return [$user, $eglise];
    }

    /** @return array{dime: int, offrande: int, don: int} */
    private function idsTypesPourEglise(EgliseLocale $eglise): array
    {
        $mid = (int) $eglise->mission_id;

        return [
            'dime' => (int) TypeRecetteMission::query()->where('mission_id', $mid)->where('code', 'dime')->value('id'),
            'offrande' => (int) TypeRecetteMission::query()->where('mission_id', $mid)->where('code', 'offrande_cultuelle')->value('id'),
            'don' => (int) TypeRecetteMission::query()->where('mission_id', $mid)->where('code', 'don')->value('id'),
        ];
    }

    public function test_utilisateur_mission_sans_eglise_ne_peut_pas_creer_recap(): void
    {
        $mission = Mission::query()->create(['nom' => 'M', 'nom_court' => 'M']);
        $user = User::factory()->create([
            'mission_id' => $mission->id,
            'eglise_locale_id' => null,
            'role_id' => $this->roleId('admin_mission'),
        ]);

        $this->actingAs($user)
            ->get(route('finances.recaps.create'))
            ->assertForbidden();
    }

    public function test_tresorier_peut_creer_recap_sans_lignes(): void
    {
        [$user, $eglise] = $this->utilisateurTresorier();

        $response = $this->actingAs($user)->post(route('finances.recaps.store'), [
            'date_sabbat' => '2026-03-21',
            'semaine_sabbat' => '2',
        ]);

        $recap = RecapSabbatEglise::query()->where('eglise_locale_id', $eglise->id)->first();
        $this->assertNotNull($recap);
        $response->assertRedirect(route('finances.recaps.edit', $recap));

        $this->assertDatabaseHas('recaps_sabbat_eglise', [
            'id' => $recap->id,
            'eglise_locale_id' => $eglise->id,
            'statut' => 'brouillon',
            'semaine_sabbat' => 2,
        ]);
        $this->assertSame('2026-03-21', $recap->fresh()->date_sabbat->format('Y-m-d'));
    }

    public function test_tresorier_ne_peut_pas_dupliquer_la_date_sabbat(): void
    {
        [$user, $eglise] = $this->utilisateurTresorier();

        RecapSabbatEglise::query()->create([
            'identifiant_public' => (string) Str::uuid(),
            'eglise_locale_id' => $eglise->id,
            'date_sabbat' => '2026-03-07',
            'annee' => 2026,
            'mois' => 3,
            'statut' => 'brouillon',
        ]);

        $this->actingAs($user)
            ->post(route('finances.recaps.store'), [
                'date_sabbat' => '2026-03-07',
            ])
            ->assertSessionHasErrors('date_sabbat');
    }

    public function test_tresorier_peut_enregistrer_lignes(): void
    {
        [$user, $eglise] = $this->utilisateurTresorier();
        $ids = $this->idsTypesPourEglise($eglise);

        $this->actingAs($user)->post(route('finances.recaps.store'), [
            'date_sabbat' => '2026-04-05',
        ]);

        $recap = RecapSabbatEglise::query()->where('eglise_locale_id', $eglise->id)->first();
        $this->assertNotNull($recap);

        $this->actingAs($user)->put(route('finances.recaps.update', $recap), [
            'semaine_sabbat' => '1',
            'lignes_assemblee' => [
                ['type_recette_id' => (string) $ids['dime'], 'montant' => '10000'],
            ],
            'lignes_individuelles' => [
                [
                    'membre_id' => '',
                    'nom_visiteur' => 'Visiteur',
                    'type_recette_id' => (string) $ids['offrande'],
                    'montant' => '20',
                ],
            ],
        ])->assertRedirect(route('finances.recaps.edit', $recap));

        $recap->refresh();
        $this->assertSame('brouillon', $recap->statut);

        $this->assertDatabaseHas('lignes_dime_offrande_recap', [
            'recap_sabbat_eglise_id' => $recap->id,
            'nom_visiteur' => 'Visiteur',
            'type_revenu' => 'offrande',
            'dimes' => '0.00',
            'offrandes' => '20.00',
        ]);
    }

    public function test_tresorier_peut_enregistrer_ligne_liee_a_un_membre(): void
    {
        [$user, $eglise] = $this->utilisateurTresorier();
        $ids = $this->idsTypesPourEglise($eglise);
        $membre = Membre::factory()->forEglise($eglise)->create([
            'nom' => 'KIMBEMBE',
            'prenom' => 'Paul',
        ]);

        $this->actingAs($user)->post(route('finances.recaps.store'), [
            'date_sabbat' => '2026-05-10',
        ]);

        $recap = RecapSabbatEglise::query()->where('eglise_locale_id', $eglise->id)->first();
        $this->assertNotNull($recap);

        $this->actingAs($user)->put(route('finances.recaps.update', $recap), [
            'lignes_assemblee' => [
                ['type_recette_id' => '', 'montant' => ''],
            ],
            'lignes_individuelles' => [
                [
                    'membre_id' => (string) $membre->id,
                    'nom_visiteur' => '',
                    'type_recette_id' => (string) $ids['dime'],
                    'montant' => '5000',
                ],
                [
                    'membre_id' => (string) $membre->id,
                    'nom_visiteur' => '',
                    'type_recette_id' => (string) $ids['don'],
                    'montant' => '150',
                ],
            ],
        ])->assertRedirect(route('finances.recaps.edit', $recap));

        $this->assertDatabaseHas('lignes_dime_offrande_recap', [
            'recap_sabbat_eglise_id' => $recap->id,
            'membre_id' => $membre->id,
            'nom_visiteur' => null,
            'type_revenu' => 'dime',
        ]);
        $this->assertDatabaseHas('lignes_dime_offrande_recap', [
            'recap_sabbat_eglise_id' => $recap->id,
            'membre_id' => $membre->id,
            'type_revenu' => 'don',
        ]);
    }

    public function test_tresorier_peut_creer_recap_avec_ligne_assemblee_initiale(): void
    {
        [$user, $eglise] = $this->utilisateurTresorier();
        $ids = $this->idsTypesPourEglise($eglise);
        Membre::factory()->forEglise($eglise)->create();

        $this->actingAs($user)->post(route('finances.recaps.store'), [
            'date_sabbat' => '2026-06-01',
            'lignes_assemblee' => [
                ['type_recette_id' => (string) $ids['offrande'], 'montant' => '75,00 fcfa'],
            ],
        ])->assertRedirect();

        $recap = RecapSabbatEglise::query()->where('eglise_locale_id', $eglise->id)->first();
        $this->assertNotNull($recap);
        $this->assertDatabaseHas('lignes_dime_offrande_recap', [
            'recap_sabbat_eglise_id' => $recap->id,
            'membre_id' => null,
            'type_revenu' => 'offrande',
        ]);
    }
}
