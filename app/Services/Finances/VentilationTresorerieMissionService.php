<?php

namespace App\Services\Finances;

use App\Models\AggregatSyntheseMissionMensuelle;
use App\Models\MissionTresorerieRapportLigneMontant;
use App\Models\MissionTresorerieRapportMensuel;
use App\Models\MissionTresorerieVentilationLigne;
use Illuminate\Support\Facades\DB;

final class VentilationTresorerieMissionService
{
    /**
     * @return array{dimes_eglises: float, offrandes_mois: float}
     */
    public function suggererDepuisAggregat(int $missionId, int $annee, int $mois): array
    {
        $agg = AggregatSyntheseMissionMensuelle::query()
            ->where('mission_id', $missionId)
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->first();

        return [
            'dimes_eglises' => $agg ? round((float) $agg->dimes, 2) : 0.0,
            'offrandes_mois' => $agg ? round((float) $agg->offrande_ecole_sabbat, 2) : 0.0,
        ];
    }

    /**
     * @return array<int, array{mois: float, cumule: float}>
     */
    public function calculerMontantsParLigne(MissionTresorerieRapportMensuel $rapport): array
    {
        $rapport->loadMissing('mission.tresorerieVentilationLignes');

        $totalDimes = $rapport->totalDimesMois();
        $totalOffrandes = round((float) $rapport->offrandes_mois, 2);

        $montantsParCode = [];
        $resultat = [];

        $lignes = $rapport->mission->tresorerieVentilationLignes->sortBy('ordre')->values();

        foreach ($lignes as $ligne) {
            if ($ligne->estTitre()) {
                continue;
            }

            $montantMois = match ($ligne->kind) {
                MissionTresorerieVentilationLigne::KIND_POURCENTAGE_DIMES => round($totalDimes * (float) $ligne->pourcentage / 100, 2),
                MissionTresorerieVentilationLigne::KIND_POURCENTAGE_OFFRANDES => round($totalOffrandes * (float) $ligne->pourcentage / 100, 2),
                MissionTresorerieVentilationLigne::KIND_SOMME_CODES => $this->sommeCodes($montantsParCode, $ligne->somme_codes ?? []),
                default => 0.0,
            };

            if ($ligne->code) {
                $montantsParCode[$ligne->code] = $montantMois;
            }

            $resultat[$ligne->id] = ['mois' => $montantMois, 'cumule' => 0.0];
        }

        $prevByLigne = $this->cumulesMoisPrecedent($rapport);

        foreach ($resultat as $ligneId => $row) {
            $prev = (float) ($prevByLigne[$ligneId] ?? 0);
            $resultat[$ligneId]['cumule'] = round($prev + $row['mois'], 2);
        }

        return $resultat;
    }

    /**
     * @param  array<string, float>  $montantsParCode
     * @param  array<int, string>  $codes
     */
    private function sommeCodes(array $montantsParCode, array $codes): float
    {
        $s = 0.0;
        foreach ($codes as $code) {
            $s += $montantsParCode[$code] ?? 0.0;
        }

        return round($s, 2);
    }

    /**
     * @return array<int, float> ligne_id => montant_cumule fin mois précédent
     */
    private function cumulesMoisPrecedent(MissionTresorerieRapportMensuel $rapport): array
    {
        $annee = (int) $rapport->annee;
        $mois = (int) $rapport->mois;
        if ($mois > 1) {
            $prevAnnee = $annee;
            $prevMois = $mois - 1;
        } else {
            $prevAnnee = $annee - 1;
            $prevMois = 12;
        }

        $prev = MissionTresorerieRapportMensuel::query()
            ->where('mission_id', $rapport->mission_id)
            ->where('annee', $prevAnnee)
            ->where('mois', $prevMois)
            ->first();

        if ($prev === null) {
            return [];
        }

        return $prev->ligneMontants()
            ->get()
            ->mapWithKeys(fn (MissionTresorerieRapportLigneMontant $m) => [(int) $m->ligne_id => (float) $m->montant_cumule])
            ->all();
    }

    /**
     * @return array<int, float> ligne_id => montant cumulé fin mois précédent (pour affichage colonne « précédent »)
     */
    public function montantsCumulesMoisPrecedentPourAffichage(MissionTresorerieRapportMensuel $rapport): array
    {
        return $this->cumulesMoisPrecedent($rapport);
    }

    /**
     * @return array<int, array{precedent: float, mois: float, cumule: float}>
     */
    public function preparerAffichage(MissionTresorerieRapportMensuel $rapport): array
    {
        $rapport->loadMissing('mission.tresorerieVentilationLignes', 'ligneMontants');

        $prev = $this->montantsCumulesMoisPrecedentPourAffichage($rapport);
        $byLigneId = [];

        if ($rapport->exists && $rapport->ligneMontants->isNotEmpty()) {
            foreach ($rapport->mission->tresorerieVentilationLignes as $ligne) {
                if ($ligne->estTitre()) {
                    continue;
                }
                $m = $rapport->ligneMontants->firstWhere('ligne_id', $ligne->id);
                $pr = (float) ($prev[$ligne->id] ?? 0);
                $byLigneId[$ligne->id] = [
                    'precedent' => $pr,
                    'mois' => $m ? (float) $m->montant_mois : 0.0,
                    'cumule' => $m ? (float) $m->montant_cumule : $pr,
                ];
            }

            return $byLigneId;
        }

        $calc = $this->calculerMontantsParLigne($rapport);
        foreach ($calc as $ligneId => $vals) {
            $byLigneId[$ligneId] = [
                'precedent' => (float) ($prev[$ligneId] ?? 0),
                'mois' => $vals['mois'],
                'cumule' => $vals['cumule'],
            ];
        }

        return $byLigneId;
    }

    public function enregistrerRapport(MissionTresorerieRapportMensuel $rapport): void
    {
        DB::transaction(function () use ($rapport): void {
            $rapport->save();

            $calcul = $this->calculerMontantsParLigne($rapport);

            MissionTresorerieRapportLigneMontant::query()
                ->where('rapport_id', $rapport->id)
                ->delete();

            foreach ($calcul as $ligneId => $vals) {
                MissionTresorerieRapportLigneMontant::query()->create([
                    'rapport_id' => $rapport->id,
                    'ligne_id' => $ligneId,
                    'montant_mois' => $vals['mois'],
                    'montant_cumule' => $vals['cumule'],
                ]);
            }
        });
    }
}
