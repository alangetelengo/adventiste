<?php

namespace App\Services\Finances;

use App\Models\LigneDimeOffrandeRecap;
use App\Models\MissionReglesVentilationRecettes;
use App\Models\RecapSabbatEglise;
use App\Models\TypeRecetteMission;
use Illuminate\Support\Collection;

/**
 * Répartition mission / église locale à partir des lignes du récap et des pourcentages
 * configurés par mission (voir MissionReglesVentilationRecettes).
 *
 * Valeurs par défaut si aucune configuration : dîme 100 % mission, offrande 50/50, don 0 % mission.
 */
final class CalculateurMontantsRecapSabbat
{
    private function __construct(
        private readonly RecapSabbatEglise $recap,
        private readonly bool $pourRapportOfficiel
    ) {}

    public static function pour(RecapSabbatEglise $recap, bool $pourRapportOfficiel = false): self
    {
        $recap->loadMissing([
            'lignesContributions.typeRecette',
            'egliseLocale.mission.reglesVentilationRecettes',
        ]);

        return new self($recap, $pourRapportOfficiel);
    }

    private function regles(): ?MissionReglesVentilationRecettes
    {
        return $this->recap->egliseLocale?->mission?->reglesVentilationRecettes;
    }

    private function pctMissionDime(): float
    {
        $v = $this->regles()?->part_mission_dime_pct;

        return $v !== null ? (float) $v : 100.0;
    }

    private function pctMissionOffrande(): float
    {
        $v = $this->regles()?->part_mission_offrande_pct;

        return $v !== null ? (float) $v : 50.0;
    }

    private function pctMissionDon(): float
    {
        $v = $this->regles()?->part_mission_don_pct;

        return $v !== null ? (float) $v : 0.0;
    }

    /** @return Collection<int, LigneDimeOffrandeRecap> */
    private function lignes(): Collection
    {
        $toutes = $this->recap->lignesContributions;
        if (! $this->pourRapportOfficiel) {
            return $toutes;
        }

        return $toutes->filter(
            fn (LigneDimeOffrandeRecap $l) => ! $this->estExclueDuRapportMission($l)
        )->values();
    }

    private function typeEffectif(LigneDimeOffrandeRecap $l): string
    {
        $l->loadMissing('typeRecette');
        $cat = $l->typeRecette?->categorie;
        if ($cat === TypeRecetteMission::CATEGORIE_DIME) {
            return LigneDimeOffrandeRecap::TYPE_DIME;
        }
        if ($cat === TypeRecetteMission::CATEGORIE_DON) {
            return LigneDimeOffrandeRecap::TYPE_DON;
        }
        if ($cat === TypeRecetteMission::CATEGORIE_OFFRANDE) {
            return LigneDimeOffrandeRecap::TYPE_OFFRANDE;
        }

        $t = $l->type_revenu;
        if (in_array($t, [LigneDimeOffrandeRecap::TYPE_DIME, LigneDimeOffrandeRecap::TYPE_OFFRANDE, LigneDimeOffrandeRecap::TYPE_DON], true)) {
            return $t;
        }
        $d = (float) $l->dimes;
        $o = (float) $l->offrandes;
        if ($d > 0 && $o <= 0) {
            return LigneDimeOffrandeRecap::TYPE_DIME;
        }
        if ($o > 0) {
            return LigneDimeOffrandeRecap::TYPE_OFFRANDE;
        }

        return LigneDimeOffrandeRecap::TYPE_OFFRANDE;
    }

    private function estExclueDuRapportMission(LigneDimeOffrandeRecap $l): bool
    {
        $l->loadMissing('typeRecette');

        return (bool) ($l->typeRecette?->exclure_rapport_mission ?? false);
    }

    private function estMissionSansPartage(LigneDimeOffrandeRecap $l): bool
    {
        $l->loadMissing('typeRecette');

        return (bool) ($l->typeRecette?->mission_sans_partage ?? false);
    }

    private function montantBrutLigne(LigneDimeOffrandeRecap $l): float
    {
        return (float) $l->dimes + (float) $l->offrandes;
    }

    private function partMissionLigne(LigneDimeOffrandeRecap $l): float
    {
        $type = $this->typeEffectif($l);

        if ($this->estMissionSansPartage($l)) {
            return round($this->montantBrutLigne($l), 2);
        }

        if ($type === LigneDimeOffrandeRecap::TYPE_DON) {
            if ($l->destination_don === LigneDimeOffrandeRecap::DESTINATION_DON_MISSION) {
                return round((float) $l->offrandes, 2);
            }
            if ($l->destination_don === LigneDimeOffrandeRecap::DESTINATION_DON_LOCALE) {
                return 0.0;
            }
        }

        return match ($type) {
            LigneDimeOffrandeRecap::TYPE_DIME => round((float) $l->dimes * $this->pctMissionDime() / 100, 2),
            LigneDimeOffrandeRecap::TYPE_DON => round((float) $l->offrandes * $this->pctMissionDon() / 100, 2),
            default => round((float) $l->offrandes * $this->pctMissionOffrande() / 100, 2),
        };
    }

    public function totalDimes(): float
    {
        return round(
            (float) $this->lignes()->sum('dimes'),
            2
        );
    }

    /** Total des offrandes cultuelles (hors dons). */
    public function totalOffrandesCultuelles(): float
    {
        $s = 0.0;
        foreach ($this->lignes() as $l) {
            if ($this->typeEffectif($l) === LigneDimeOffrandeRecap::TYPE_OFFRANDE) {
                $s += (float) $l->offrandes;
            }
        }

        return round($s, 2);
    }

    public function totalDons(): float
    {
        $s = 0.0;
        foreach ($this->lignes() as $l) {
            if ($this->typeEffectif($l) === LigneDimeOffrandeRecap::TYPE_DON) {
                $s += (float) $l->offrandes;
            }
        }

        return round($s, 2);
    }

    /** Part des offrandes cultuelles affectée à la mission (selon % mission). */
    public function moitiePourMissionSurOffrandes(): float
    {
        $s = 0.0;
        foreach ($this->lignes() as $l) {
            if ($this->typeEffectif($l) !== LigneDimeOffrandeRecap::TYPE_OFFRANDE) {
                continue;
            }
            if ($this->estMissionSansPartage($l)) {
                continue;
            }
            $s += $this->partMissionLigne($l);
        }

        return round($s, 2);
    }

    /** Total restant au budget église locale après application des %. */
    public function partBudgetEgliseLocale(): float
    {
        $s = 0.0;
        foreach ($this->lignes() as $l) {
            $s += max(0, $this->montantBrutLigne($l) - $this->partMissionLigne($l));
        }

        return round($s, 2);
    }

    public function totalATransfererMission(float $autresOffrandesMission = 0.0): float
    {
        $versMission = 0.0;
        foreach ($this->lignes() as $l) {
            $versMission += $this->partMissionLigne($l);
        }

        return round($versMission + $autresOffrandesMission, 2);
    }

    public function montantTotalBrut(): float
    {
        return round(
            $this->totalDimes() + $this->totalOffrandesCultuelles() + $this->totalDons(),
            2
        );
    }

    public function libelleRapportDime(): string
    {
        $lib = $this->regles()?->libelle_rapport_dime;

        return (is_string($lib) && trim($lib) !== '') ? trim($lib) : 'Dîme';
    }

    public function libelleRapportOffrande(): string
    {
        $lib = $this->regles()?->libelle_rapport_offrande;

        return (is_string($lib) && trim($lib) !== '') ? trim($lib) : 'Offrande cultuelle';
    }

    public function libelleRapportDon(): string
    {
        $lib = $this->regles()?->libelle_rapport_don;

        return (is_string($lib) && trim($lib) !== '') ? trim($lib) : 'Don';
    }
}
