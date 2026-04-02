<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneAutresDime extends Model
{
    protected $table = 'lignes_autres_dimes';

    protected $fillable = [
        'rapport_station_mission_id',
        'code_categorie',
        'montant_mois',
    ];

    protected function casts(): array
    {
        return [
            'montant_mois' => 'decimal:2',
        ];
    }

    public function rapportStation(): BelongsTo
    {
        return $this->belongsTo(RapportStationMission::class, 'rapport_station_mission_id');
    }
}
