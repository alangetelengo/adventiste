<?php

namespace Database\Seeders;

use App\Models\EgliseLocale;
use App\Models\Mission;
use Illuminate\Database\Seeder;

/**
 * Données issues du tableau « État des dîmes des églises » (objectifs et collectes 2024–2025).
 */
class EglisesLocalesEtatDimes2025Seeder extends Seeder
{
    public function run(): void
    {
        $mission = Mission::query()->firstOrCreate(
            ['nom' => 'Mission du Congo Brazzaville'],
            ['nom_court' => 'MCB']
        );

        foreach ($this->lignes() as $ligne) {
            EgliseLocale::query()->updateOrCreate(
                ['code_unique' => $ligne['code_unique']],
                [
                    'mission_id' => $mission->id,
                    'district_id' => null,
                    'nom' => $ligne['nom'],
                    'actif' => true,
                    'indicateurs_financiers' => $ligne['indicateurs'],
                ]
            );
        }
    }

    /**
     * Montants en FCFA (entiers). Les clés absentes ou null = non renseigné dans la source.
     *
     * @return list<array{nom: string, code_unique: string, indicateurs: array<string, int|float|string|null>}>
     */
    private function lignes(): array
    {
        $anneeRef = 2025;

        return [
            $this->ligne('BACONGO', 'MCB-BACONGO', $anneeRef, 25766118, 0, 4043060, 22363320, 2147177, 75),
            $this->ligne('MASSENGO', 'MCB-MASSENGO', $anneeRef, 16727555, 0, 4141520, 14545700, 1393963, 265),
            $this->ligne('TALANGAI', 'MCB-TALANGAI', $anneeRef, 6800157, 0, 3272500, 5913180, 566680, 167),
            $this->ligne('INONI', 'MCB-INONI', $anneeRef, 2436304, 0, 1545350, 2118525, 203025, 116),
            $this->ligne('IMPFONDO', 'MCB-IMPFONDO', $anneeRef, 760535, 0, 756940, 1530900, 63378, 81),
            $this->ligne('NKOUÏKOU', 'MCB-NKOU-IKOU', $anneeRef, 1658818, 0, 542250, 1442450, 138235, 103),
            $this->ligne('OYO', 'MCB-OYO', $anneeRef, 2203113, 0, 889300, 1915750, 183593, 42),
            $this->ligne('OUESSO', 'MCB-OUESSO', $anneeRef, 1449604, 0, 628320, 1260525, 120800, 59),
            $this->ligne('KINTELE', 'MCB-KINTELE', $anneeRef, 507898, 0, 367150, 441650, 42325, 88),
            $this->ligne('POKOLA', 'MCB-POKOLA', $anneeRef, 766906, 0, 255200, 666875, 63909, 79),
            $this->ligne('NGO', 'MCB-NGO', $anneeRef, 433205, 0, 107550, 376700, 36100, 22),
            $this->ligne('LOUKOLELA', 'MCB-LOUKOLELA', $anneeRef, 230390, 0, 214800, 200600, 19199, 37),
            $this->ligne('COTE MATEV', 'MCB-COTE-MATEV', $anneeRef, 434355, 0, 223850, 377700, 36196, 21),
            $this->ligne('BOLEKO', 'MCB-BOLEKO', $anneeRef, 339739, 0, 88650, 295425, 28312, 29),
            $this->ligne('GAMBOMA', 'MCB-GAMBOMA', $anneeRef, 86854, 31550, 0, 75525, 7238, 14),
            $this->ligne('OLLOMBO', 'MCB-OLLOMBO', $anneeRef, 61180, 0, 17650, 53200, 5098, 22),
            $this->ligne('PK45', 'MCB-PK45', $anneeRef, 650095, 0, 295150, 563300, 54175, 68),
            $this->ligne('EWO', 'MCB-EWO', $anneeRef, 94300, 0, 0, 88000, 7858, 4),
            $this->ligne('DOLISIE', 'MCB-DOLISIE', $anneeRef, 68109, 0, 29450, 59225, 5676, 20),
            $this->ligne('KONDA', 'MCB-KONDA', $anneeRef, 99993, 0, 80800, 86950, 8333, 24),
            $this->ligne('NKAYI', 'MCB-NKAYI', $anneeRef, 51721, 0, 0, 44975, 4310, 10),
            $this->ligne('MOSSAKA', 'MCB-MOSSAKA', $anneeRef, 0, 0, 0, 0, 0, 10),
            $this->ligne('NGOMBE', 'MCB-NGOMBE', $anneeRef, 0, 0, 0, 0, 0, 19),
            [
                'nom' => 'OMS',
                'code_unique' => 'MCB-OMS',
                'indicateurs' => [
                    'source' => 'État des dîmes des églises',
                    'annee_objectif' => $anneeRef,
                    'annee_dimes_reference' => 2024,
                    'objectif_dimes' => null,
                    'dimes_collectees' => null,
                    'offrandes_collectees' => null,
                    'dimes_annee_precedente' => null,
                    'dimes_moyenne_mensuelle' => null,
                    'nombre_membres' => null,
                ],
            ],
            [
                'nom' => 'BOTOUNOU',
                'code_unique' => 'MCB-BOTOUNOU',
                'indicateurs' => [
                    'source' => 'État des dîmes des églises',
                    'annee_objectif' => $anneeRef,
                    'annee_dimes_reference' => 2024,
                    'objectif_dimes' => null,
                    'dimes_collectees' => 0,
                    'offrandes_collectees' => null,
                    'dimes_annee_precedente' => null,
                    'dimes_moyenne_mensuelle' => null,
                    'nombre_membres' => null,
                ],
            ],
        ];
    }

    /**
     * @return array{nom: string, code_unique: string, indicateurs: array<string, int|float|string>}
     */
    private function ligne(
        string $nom,
        string $codeUnique,
        int $anneeObjectif,
        int $objectifDimes,
        int $dimesCollectees,
        int $offrandesCollectees,
        int $dimesAnneePrecedente,
        int $dimesMoyenneMensuelle,
        int $nombreMembres
    ): array {
        return [
            'nom' => $nom,
            'code_unique' => $codeUnique,
            'indicateurs' => [
                'source' => 'État des dîmes des églises',
                'annee_objectif' => $anneeObjectif,
                'annee_dimes_reference' => 2024,
                'objectif_dimes' => $objectifDimes,
                'dimes_collectees' => $dimesCollectees,
                'offrandes_collectees' => $offrandesCollectees,
                'dimes_annee_precedente' => $dimesAnneePrecedente,
                'dimes_moyenne_mensuelle' => $dimesMoyenneMensuelle,
                'nombre_membres' => $nombreMembres,
            ],
        ];
    }
}
