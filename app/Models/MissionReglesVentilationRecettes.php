<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionReglesVentilationRecettes extends Model
{
    protected $table = 'mission_regles_ventilation_recettes';

    protected $fillable = [
        'mission_id',
        'part_mission_dime_pct',
        'part_mission_offrande_pct',
        'part_mission_don_pct',
        'libelle_rapport_dime',
        'libelle_rapport_offrande',
        'libelle_rapport_don',
        'notes_internes',
    ];

    protected function casts(): array
    {
        return [
            'part_mission_dime_pct' => 'decimal:2',
            'part_mission_offrande_pct' => 'decimal:2',
            'part_mission_don_pct' => 'decimal:2',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }
}
