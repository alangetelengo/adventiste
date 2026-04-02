<?php

namespace App\Services\Finances;

use App\Models\AggregatSyntheseMissionMensuelle;
use App\Models\EgliseLocale;
use App\Models\EntreeFinanciereGroupeMission;
use App\Models\LigneSyntheseMensuelleEglise;
use App\Models\RapportMensuelEglise;
use App\Models\RapportMensuelLigneSabbat;
use App\Models\RecapSabbatEglise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RapportMensuelSyntheseService
{
    private const MAX_SABBATHS_PAR_MOIS = 5;

    /**
     * Régénère le rapport mensuel, les lignes sabbat, la grille de synthèse et l'agrégat mission.
     */
    public function regenererPourEgliseEtMois(EgliseLocale $eglise, int $annee, int $mois): RapportMensuelEglise
    {
        return DB::transaction(function () use ($eglise, $annee, $mois) {
            $rapport = RapportMensuelEglise::query()->firstOrCreate(
                [
                    'eglise_locale_id' => $eglise->id,
                    'annee' => $annee,
                    'mois' => $mois,
                ],
                [
                    'identifiant_public' => (string) Str::uuid(),
                ]
            );

            if ($rapport->verrouille_le !== null) {
                return $rapport;
            }

            $rapport->lignesSabbat()->delete();
            $rapport->lignesSynthese()->delete();

            $recaps = RecapSabbatEglise::query()
                ->where('eglise_locale_id', $eglise->id)
                ->where('annee', $annee)
                ->where('mois', $mois)
                ->orderBy('date_sabbat')
                ->get();

            $sommeDimes = 0.0;
            $sommeMoitie = 0.0;
            $sommeAutresMission = 0.0;
            $sommeTransfer = 0.0;

            $indice = 1;
            foreach ($recaps as $recap) {
                if ($indice > self::MAX_SABBATHS_PAR_MOIS) {
                    break;
                }

                $calc = CalculateurMontantsRecapSabbat::pour($recap, pourRapportOfficiel: true);
                $autresMission = 0.0;

                $totalDimes = $calc->totalDimes();
                $moitie = $calc->moitiePourMissionSurOffrandes();
                $transfer = $calc->totalATransfererMission($autresMission);

                RapportMensuelLigneSabbat::query()->create([
                    'rapport_mensuel_eglise_id' => $rapport->id,
                    'indice_sabbat_dans_mois' => $indice,
                    'date_sabbat' => $recap->date_sabbat,
                    'total_dimes' => $totalDimes,
                    'moitie_offrandes' => $moitie,
                    'autres_offrandes_mission' => $autresMission,
                    'total_transferer_mission' => $transfer,
                ]);

                LigneSyntheseMensuelleEglise::query()->create([
                    'rapport_mensuel_eglise_id' => $rapport->id,
                    'indice_sabbat' => $indice,
                    'date_sabbat' => $recap->date_sabbat,
                    'dimes' => $totalDimes,
                    // Libellé historique de colonne : ici = total offrandes cultuelles du sabbat (avant partage 50/50).
                    'offrande_ecole_sabbat' => $calc->totalOffrandesCultuelles(),
                    'fonds_missionnaires' => $moitie,
                    'budget_eglise_locale' => $calc->partBudgetEgliseLocale(),
                    'autres_offrandes' => $calc->totalDons(),
                    'montant_total' => $calc->montantTotalBrut(),
                ]);

                $sommeDimes += $totalDimes;
                $sommeMoitie += $moitie;
                $sommeAutresMission += $autresMission;
                $sommeTransfer += $transfer;
                $indice++;
            }

            $this->creerLigneSyntheseTotale($rapport);

            $rapport->update([
                'total_dimes_mois' => round($sommeDimes, 2),
                'total_moitie_offrandes_mois' => round($sommeMoitie, 2),
                'total_autres_offrandes_mission_mois' => round($sommeAutresMission, 2),
                'total_a_transferer_mission_mois' => round($sommeTransfer, 2),
                'regenere_le' => now(),
            ]);

            $this->regenererAggregatMission($eglise->mission_id, $annee, $mois);

            return $rapport->fresh(['lignesSabbat', 'lignesSynthese']);
        });
    }

    /**
     * Recalcule uniquement l'agrégat mission (églises + groupes) pour la période.
     */
    public function regenererAggregatMission(int $missionId, int $annee, int $mois): AggregatSyntheseMissionMensuelle
    {
        $totaux = [
            'dimes' => 0.0,
            'offrande_ecole_sabbat' => 0.0,
            'budget_eglise_locale' => 0.0,
            'fonds_missionnaires' => 0.0,
            'autres_offrandes' => 0.0,
            'montant_total' => 0.0,
        ];

        $lignesTotaux = LigneSyntheseMensuelleEglise::query()
            ->where('indice_sabbat', 0)
            ->whereHas('rapportMensuel.egliseLocale', fn ($q) => $q->where('mission_id', $missionId))
            ->whereHas('rapportMensuel', fn ($q) => $q->where('annee', $annee)->where('mois', $mois))
            ->get();

        foreach ($lignesTotaux as $ligne) {
            $totaux['dimes'] += (float) $ligne->dimes;
            $totaux['offrande_ecole_sabbat'] += (float) $ligne->offrande_ecole_sabbat;
            $totaux['budget_eglise_locale'] += (float) $ligne->budget_eglise_locale;
            $totaux['fonds_missionnaires'] += (float) $ligne->fonds_missionnaires;
            $totaux['autres_offrandes'] += (float) $ligne->autres_offrandes;
            $totaux['montant_total'] += (float) $ligne->montant_total;
        }

        $entreesGroupes = EntreeFinanciereGroupeMission::query()
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->whereHas('groupeMission', fn ($q) => $q->where('mission_id', $missionId))
            ->get();

        foreach ($entreesGroupes as $e) {
            $totaux['dimes'] += (float) $e->dimes;
            $totaux['offrande_ecole_sabbat'] += (float) $e->offrande_ecole_sabbat;
            $totaux['budget_eglise_locale'] += (float) $e->offrande_budget_eglise;
            $totaux['fonds_missionnaires'] += (float) $e->offrande_fonds_mission;
            $totaux['autres_offrandes'] += (float) $e->offrande_autres;
            $totaux['montant_total'] += (float) $e->dimes
                + (float) $e->offrande_ecole_sabbat
                + (float) $e->offrande_budget_eglise
                + (float) $e->offrande_fonds_mission
                + (float) $e->offrande_autres;
        }

        foreach ($totaux as $k => $v) {
            $totaux[$k] = round($v, 2);
        }

        return AggregatSyntheseMissionMensuelle::query()->updateOrCreate(
            [
                'mission_id' => $missionId,
                'annee' => $annee,
                'mois' => $mois,
            ],
            [
                ...$totaux,
                'calcule_le' => now(),
            ]
        );
    }

    private function creerLigneSyntheseTotale(RapportMensuelEglise $rapport): void
    {
        $lignes = LigneSyntheseMensuelleEglise::query()
            ->where('rapport_mensuel_eglise_id', $rapport->id)
            ->where('indice_sabbat', '>', 0)
            ->get();

        if ($lignes->isEmpty()) {
            LigneSyntheseMensuelleEglise::query()->create([
                'rapport_mensuel_eglise_id' => $rapport->id,
                'indice_sabbat' => 0,
                'date_sabbat' => null,
                'dimes' => 0,
                'offrande_ecole_sabbat' => 0,
                'budget_eglise_locale' => 0,
                'fonds_missionnaires' => 0,
                'autres_offrandes' => 0,
                'montant_total' => 0,
            ]);

            return;
        }

        LigneSyntheseMensuelleEglise::query()->create([
            'rapport_mensuel_eglise_id' => $rapport->id,
            'indice_sabbat' => 0,
            'date_sabbat' => null,
            'dimes' => round($lignes->sum('dimes'), 2),
            'offrande_ecole_sabbat' => round($lignes->sum('offrande_ecole_sabbat'), 2),
            'budget_eglise_locale' => round($lignes->sum('budget_eglise_locale'), 2),
            'fonds_missionnaires' => round($lignes->sum('fonds_missionnaires'), 2),
            'autres_offrandes' => round($lignes->sum('autres_offrandes'), 2),
            'montant_total' => round($lignes->sum('montant_total'), 2),
        ]);
    }
}
