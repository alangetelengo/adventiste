<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionTresorerieRapportMensuel extends Model
{
    public const ETAT_BROUILLON = 'brouillon';
    public const ETAT_SOUMIS = 'soumis';
    public const ETAT_VALIDE_MISSION = 'valide_mission';
    public const ETAT_REFUSE_MISSION = 'refuse_mission';

    protected $table = 'mission_tresorerie_rapports_mensuels';

    protected $attributes = [
        'etat_transmission' => self::ETAT_BROUILLON,
    ];

    protected $fillable = [
        'mission_id',
        'annee',
        'mois',
        'dimes_eglises',
        'autres_dimes',
        'offrandes_mois',
        'etat_transmission',
        'soumis_le',
        'soumis_par_user_id',
        'mission_revu_le',
        'mission_revu_par_user_id',
        'mission_commentaire',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'mois' => 'integer',
            'dimes_eglises' => 'decimal:2',
            'autres_dimes' => 'decimal:2',
            'offrandes_mois' => 'decimal:2',
            'soumis_le' => 'datetime',
            'mission_revu_le' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function labelsEtatsTransmission(): array
    {
        return [
            self::ETAT_BROUILLON => __('finances.mission_tresorerie_rapport.etat_brouillon'),
            self::ETAT_SOUMIS => __('finances.mission_tresorerie_rapport.etat_soumis'),
            self::ETAT_VALIDE_MISSION => __('finances.mission_tresorerie_rapport.etat_valide'),
            self::ETAT_REFUSE_MISSION => __('finances.mission_tresorerie_rapport.etat_refuse'),
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function ligneMontants(): HasMany
    {
        return $this->hasMany(MissionTresorerieRapportLigneMontant::class, 'rapport_id');
    }

    public function totalDimesMois(): float
    {
        return round((float) $this->dimes_eglises + (float) $this->autres_dimes, 2);
    }

    public function totalRecettesMois(): float
    {
        return round($this->totalDimesMois() + (float) $this->offrandes_mois, 2);
    }
}
