<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RapportStationMission extends Model
{
    protected $table = 'rapports_station_mission';

    protected $fillable = [
        'mission_id',
        'annee',
        'mois',
        'dernier_remplissage_auto_le',
    ];

    protected function casts(): array
    {
        return [
            'dernier_remplissage_auto_le' => 'datetime',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneRapportStationMission::class, 'rapport_station_mission_id');
    }

    public function lignesAutresDimes(): HasMany
    {
        return $this->hasMany(LigneAutresDime::class, 'rapport_station_mission_id');
    }
}
