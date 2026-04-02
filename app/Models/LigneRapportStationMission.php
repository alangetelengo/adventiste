<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneRapportStationMission extends Model
{
    protected $table = 'lignes_rapport_station_mission';

    protected $fillable = [
        'rapport_station_mission_id',
        'code_ligne',
        'pourcentage',
        'montant_mois',
        'montant_periode_precedente',
        'montant_cumule',
        'montant_mois_saisi_manuel',
        'ordre_tri',
    ];

    protected function casts(): array
    {
        return [
            'pourcentage' => 'decimal:4',
            'montant_mois' => 'decimal:2',
            'montant_periode_precedente' => 'decimal:2',
            'montant_cumule' => 'decimal:2',
            'montant_mois_saisi_manuel' => 'boolean',
        ];
    }

    public function rapportStation(): BelongsTo
    {
        return $this->belongsTo(RapportStationMission::class, 'rapport_station_mission_id');
    }
}
