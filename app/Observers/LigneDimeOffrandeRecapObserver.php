<?php

namespace App\Observers;

use App\Models\EgliseLocale;
use App\Models\LigneDimeOffrandeRecap;
use App\Models\RecapSabbatEglise;
use App\Services\Finances\RapportMensuelSyntheseService;

class LigneDimeOffrandeRecapObserver
{
    public function __construct(
        private readonly RapportMensuelSyntheseService $rapportMensuelSyntheseService
    ) {}

    public function saved(LigneDimeOffrandeRecap $ligne): void
    {
        $this->apresChangement($ligne);
    }

    public function deleted(LigneDimeOffrandeRecap $ligne): void
    {
        $recapId = (int) $ligne->getAttribute('recap_sabbat_eglise_id');
        $recap = RecapSabbatEglise::query()->find($recapId);
        if ($recap === null) {
            return;
        }

        $recap->synchroniserStatutDepuisLignes();

        $eglise = $recap->egliseLocale;
        if ($eglise instanceof EgliseLocale) {
            $this->rapportMensuelSyntheseService->regenererPourEgliseEtMois(
                $eglise,
                (int) $recap->annee,
                (int) $recap->mois
            );
        }
    }

    private function apresChangement(LigneDimeOffrandeRecap $ligne): void
    {
        $ligne->loadMissing('recapSabbat.egliseLocale');
        $recap = $ligne->recapSabbat;
        if ($recap === null) {
            return;
        }

        $recap->synchroniserStatutDepuisLignes();

        $eglise = $recap->egliseLocale;
        if ($eglise instanceof EgliseLocale) {
            $this->rapportMensuelSyntheseService->regenererPourEgliseEtMois(
                $eglise,
                (int) $recap->annee,
                (int) $recap->mois
            );
        }
    }
}
