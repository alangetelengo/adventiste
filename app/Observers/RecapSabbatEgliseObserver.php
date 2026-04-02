<?php

namespace App\Observers;

use App\Models\EgliseLocale;
use App\Models\RecapSabbatEglise;
use App\Services\Finances\RapportMensuelSyntheseService;

class RecapSabbatEgliseObserver
{
    public function __construct(
        private readonly RapportMensuelSyntheseService $rapportMensuelSyntheseService
    ) {}

    public function saved(RecapSabbatEglise $recap): void
    {
        $this->regenerer($recap);
    }

    public function deleted(RecapSabbatEglise $recap): void
    {
        $egliseId = (int) $recap->getAttribute('eglise_locale_id');
        $eglise = EgliseLocale::query()->find($egliseId);
        if ($eglise === null) {
            return;
        }

        $this->rapportMensuelSyntheseService->regenererPourEgliseEtMois(
            $eglise,
            (int) $recap->getAttribute('annee'),
            (int) $recap->getAttribute('mois')
        );
    }

    private function regenerer(RecapSabbatEglise $recap): void
    {
        $recap->loadMissing('egliseLocale');
        $eglise = $recap->egliseLocale;
        if ($eglise === null) {
            return;
        }

        $this->rapportMensuelSyntheseService->regenererPourEgliseEtMois(
            $eglise,
            (int) $recap->annee,
            (int) $recap->mois
        );
    }
}
