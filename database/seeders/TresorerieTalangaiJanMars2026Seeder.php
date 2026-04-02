<?php

namespace Database\Seeders;

use App\Models\EgliseLocale;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\Membre;
use App\Models\RecapSabbatEglise;
use App\Models\TypeRecetteMission;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TresorerieTalangaiJanMars2026Seeder extends Seeder
{
    public function run(): void
    {
        $eglise = EgliseLocale::query()->where('code_unique', 'MCB-TALANGAI')->first();
        if ($eglise === null) {
            $this->command?->warn('Seeder trésorerie TALANGAI ignoré : église MCB-TALANGAI introuvable.');

            return;
        }

        $types = $this->resolveTypes((int) $eglise->mission_id);
        $membresTalangai = Membre::query()
            ->where('eglise_locale_id', $eglise->id)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->limit(12)
            ->pluck('id')
            ->values();

        $recaps = [
            ['date' => '2026-01-03', 'semaine' => 1, 'offrande_culte' => 120000, 'construction' => 20000],
            ['date' => '2026-01-10', 'semaine' => 2, 'offrande_culte' => 135000, 'construction' => 15000],
            ['date' => '2026-01-17', 'semaine' => 3, 'offrande_culte' => 142000, 'construction' => 18000],
            ['date' => '2026-01-24', 'semaine' => 4, 'offrande_culte' => 127000, 'construction' => 10000],
            ['date' => '2026-01-31', 'semaine' => 5, 'offrande_culte' => 148000, 'construction' => 25000],

            ['date' => '2026-02-07', 'semaine' => 1, 'offrande_culte' => 133000, 'construction' => 12000],
            ['date' => '2026-02-14', 'semaine' => 2, 'offrande_culte' => 151000, 'construction' => 22000],
            ['date' => '2026-02-21', 'semaine' => 3, 'offrande_culte' => 129000, 'construction' => 9000],
            ['date' => '2026-02-28', 'semaine' => 4, 'offrande_culte' => 140000, 'construction' => 14000],

            ['date' => '2026-03-07', 'semaine' => 1, 'offrande_culte' => 155000, 'construction' => 30000],
            ['date' => '2026-03-14', 'semaine' => 2, 'offrande_culte' => 137000, 'construction' => 16000],
            ['date' => '2026-03-21', 'semaine' => 3, 'offrande_culte' => 149000, 'construction' => 19000],
            ['date' => '2026-03-28', 'semaine' => 4, 'offrande_culte' => 162000, 'construction' => 21000, 'offrande_13e' => 45000],
        ];

        foreach ($recaps as $i => $row) {
            $date = Carbon::parse($row['date']);
            $recap = RecapSabbatEglise::query()->firstOrNew([
                'eglise_locale_id' => $eglise->id,
                'date_sabbat' => $date->toDateString(),
            ]);

            if (! $recap->exists) {
                $recap->identifiant_public = (string) Str::uuid();
            }
            $recap->annee = $date->year;
            $recap->mois = $date->month;
            $recap->semaine_sabbat = (int) $row['semaine'];
            $recap->statut = 'brouillon';
            $recap->save();

            LigneDimeOffrandeRecap::query()
                ->where('recap_sabbat_eglise_id', $recap->id)
                ->delete();

            $ordre = 1;

            // Offrande collectée pendant le culte (part mission selon règle 50/50 par défaut).
            $ordre = $this->insertLigne($recap->id, $types['offrande_cultuelle']->id, null, null, LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE, 'offrande', 0, (float) $row['offrande_culte'], $ordre);
            // Offrande de construction (exclue rapport mission).
            $ordre = $this->insertLigne($recap->id, $types['offrande_construction']->id, null, 'Offrande construction', LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE, 'offrande', 0, (float) $row['construction'], $ordre);

            if (isset($row['offrande_13e'])) {
                // 13e sabbat (100% mission sans partage).
                $ordre = $this->insertLigne($recap->id, $types['offrande_13e_sabbat']->id, null, 'Offrande 13e sabbat', LigneDimeOffrandeRecap::ORIGINE_ASSEMBLEE, 'offrande', 0, (float) $row['offrande_13e'], $ordre);
            }

            // Dîmes individuelles de quelques membres.
            for ($k = 0; $k < 3; $k++) {
                $memberId = $membresTalangai->get(($i + $k) % max(1, $membresTalangai->count()));
                $montantDime = 20000 + (($i + $k) % 7) * 2500;
                $ordre = $this->insertLigne($recap->id, $types['dime']->id, $memberId, null, LigneDimeOffrandeRecap::ORIGINE_INDIVIDUEL, 'dime', (float) $montantDime, 0, $ordre);
            }

            // Un don local et parfois un don mission pour test des règles.
            $donLocal = 5000 + ($i % 4) * 1000;
            $ordre = $this->insertLigne(
                $recap->id,
                $types['don']->id,
                $membresTalangai->get($i % max(1, $membresTalangai->count())),
                'Don local action sociale',
                LigneDimeOffrandeRecap::ORIGINE_INDIVIDUEL,
                'don',
                0,
                (float) $donLocal,
                $ordre,
                destinationDon: LigneDimeOffrandeRecap::DESTINATION_DON_LOCALE
            );

            if ($i % 3 === 0) {
                $ordre = $this->insertLigne(
                    $recap->id,
                    $types['don']->id,
                    $membresTalangai->get(($i + 1) % max(1, $membresTalangai->count())),
                    'Don mission évangélisation',
                    LigneDimeOffrandeRecap::ORIGINE_INDIVIDUEL,
                    'don',
                    0,
                    8000,
                    $ordre,
                    destinationDon: LigneDimeOffrandeRecap::DESTINATION_DON_MISSION
                );
            }
        }
    }

    /** @return array<string, TypeRecetteMission> */
    private function resolveTypes(int $missionId): array
    {
        $defaults = [
            [
                'code' => 'dime',
                'libelle' => 'Dîme',
                'categorie' => 'dime',
                'ordre' => 10,
                'exclure_rapport_mission' => false,
                'mission_sans_partage' => false,
            ],
            [
                'code' => 'offrande_cultuelle',
                'libelle' => 'Offrande culte (collecte du culte)',
                'categorie' => 'offrande',
                'ordre' => 20,
                'exclure_rapport_mission' => false,
                'mission_sans_partage' => false,
            ],
            [
                'code' => 'don',
                'libelle' => 'Don',
                'categorie' => 'don',
                'ordre' => 30,
                'exclure_rapport_mission' => false,
                'mission_sans_partage' => false,
            ],
            [
                'code' => 'offrande_construction',
                'libelle' => 'Offrande construction',
                'categorie' => 'offrande',
                'ordre' => 40,
                'exclure_rapport_mission' => true,
                'mission_sans_partage' => false,
            ],
            [
                'code' => 'offrande_13e_sabbat',
                'libelle' => 'Offrande 13e sabbat',
                'categorie' => 'offrande',
                'ordre' => 50,
                'exclure_rapport_mission' => false,
                'mission_sans_partage' => true,
            ],
        ];

        $resolved = [];
        foreach ($defaults as $row) {
            $type = TypeRecetteMission::query()->updateOrCreate(
                ['mission_id' => $missionId, 'code' => $row['code']],
                [
                    'libelle' => $row['libelle'],
                    'categorie' => $row['categorie'],
                    'ordre' => $row['ordre'],
                    'actif' => true,
                    'exclure_rapport_mission' => $row['exclure_rapport_mission'],
                    'mission_sans_partage' => $row['mission_sans_partage'],
                ]
            );
            $resolved[$row['code']] = $type;
        }

        return $resolved;
    }

    private function insertLigne(
        int $recapId,
        int $typeRecetteId,
        mixed $membreId,
        ?string $designation,
        string $origine,
        string $typeRevenu,
        float $dimes,
        float $offrandes,
        int $ordre,
        ?string $destinationDon = null
    ): int {
        LigneDimeOffrandeRecap::query()->create([
            'recap_sabbat_eglise_id' => $recapId,
            'type_recette_id' => $typeRecetteId,
            'membre_id' => $membreId,
            'nom_visiteur' => $membreId ? null : 'VISITEUR',
            'designation' => $designation,
            'mode_don' => $typeRevenu === 'don' ? LigneDimeOffrandeRecap::MODE_DON_ARGENT : null,
            'destination_don' => $typeRevenu === 'don' ? $destinationDon : null,
            'quantite_nature' => null,
            'unite_nature' => null,
            'origine' => $origine,
            'type_revenu' => $typeRevenu,
            'dimes' => $dimes,
            'offrandes' => $offrandes,
            'ordre_ligne' => $ordre,
            'statut_ligne' => LigneDimeOffrandeRecap::STATUT_BROUILLON,
        ]);

        return $ordre + 1;
    }
}
