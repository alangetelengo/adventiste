<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecapSabbatEglise extends Model
{
    protected $table = 'recaps_sabbat_eglise';

    protected $fillable = [
        'identifiant_public',
        'eglise_locale_id',
        'date_sabbat',
        'annee',
        'mois',
        'semaine_sabbat',
        'statut',
        'signe_ancien',
        'date_signature_ancien',
        'signe_diacre',
        'date_signature_diacre',
        'signe_tresorier',
        'date_signature_tresorier',
        'signe_chef_district',
        'date_signature_chef_district',
        'pieces_jointes_signatures',
    ];

    protected function casts(): array
    {
        return [
            'date_sabbat' => 'date',
            'date_signature_ancien' => 'date',
            'date_signature_diacre' => 'date',
            'date_signature_tresorier' => 'date',
            'date_signature_chef_district' => 'date',
            'signe_ancien' => 'boolean',
            'signe_diacre' => 'boolean',
            'signe_tresorier' => 'boolean',
            'signe_chef_district' => 'boolean',
            'pieces_jointes_signatures' => 'array',
            'semaine_sabbat' => 'integer',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function libellesSemainesSabbat(): array
    {
        return [
            1 => __('finances.recaps.week_label', ['n' => 1]),
            2 => __('finances.recaps.week_label', ['n' => 2]),
            3 => __('finances.recaps.week_label', ['n' => 3]),
            4 => __('finances.recaps.week_label', ['n' => 4]),
            5 => __('finances.recaps.week_label', ['n' => 5]),
        ];
    }

    public function egliseLocale(): BelongsTo
    {
        return $this->belongsTo(EgliseLocale::class, 'eglise_locale_id');
    }

    public function lignesContributions(): HasMany
    {
        return $this->hasMany(LigneDimeOffrandeRecap::class, 'recap_sabbat_eglise_id');
    }

    /**
     * Met à jour {@see $statut} selon les statuts des lignes (affichage liste, filtres).
     */
    public function synchroniserStatutDepuisLignes(): void
    {
        $this->loadMissing('lignesContributions');
        $lignes = $this->lignesContributions;

        if ($lignes->isEmpty()) {
            $nouveau = 'brouillon';
        } elseif ($lignes->every(fn (LigneDimeOffrandeRecap $l) => $l->statut_ligne === LigneDimeOffrandeRecap::STATUT_VERROUILLE)) {
            $nouveau = 'verrouille';
        } elseif ($lignes->contains(fn (LigneDimeOffrandeRecap $l) => $l->statut_ligne === LigneDimeOffrandeRecap::STATUT_SOUMIS)) {
            $nouveau = 'soumis';
        } else {
            $nouveau = 'brouillon';
        }

        if ($this->statut !== $nouveau) {
            $this->statut = $nouveau;
            $this->saveQuietly();
        }
    }

    public function peutSoumettre(): bool
    {
        $this->loadMissing('lignesContributions');
        if ($this->lignesContributions->isEmpty()) {
            return false;
        }

        if ($this->lignesContributions->contains(fn (LigneDimeOffrandeRecap $l) => $l->statut_ligne === LigneDimeOffrandeRecap::STATUT_SOUMIS)) {
            return false;
        }
        if ($this->lignesContributions->contains(fn (LigneDimeOffrandeRecap $l) => $l->statut_ligne === LigneDimeOffrandeRecap::STATUT_REJETE)) {
            return false;
        }

        return $this->lignesContributions->contains(
            fn (LigneDimeOffrandeRecap $l) => $l->statut_ligne === LigneDimeOffrandeRecap::STATUT_BROUILLON
        );
    }

    public function peutValiderParMission(): bool
    {
        $this->loadMissing('lignesContributions');
        if ($this->lignesContributions->isEmpty()) {
            return false;
        }

        if ($this->lignesContributions->contains(fn (LigneDimeOffrandeRecap $l) => in_array($l->statut_ligne, [
            LigneDimeOffrandeRecap::STATUT_BROUILLON,
            LigneDimeOffrandeRecap::STATUT_REJETE,
        ], true))) {
            return false;
        }

        return $this->lignesContributions->contains(
            fn (LigneDimeOffrandeRecap $l) => $l->statut_ligne === LigneDimeOffrandeRecap::STATUT_SOUMIS
        );
    }
}
