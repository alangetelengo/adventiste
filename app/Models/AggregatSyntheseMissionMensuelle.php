<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AggregatSyntheseMissionMensuelle extends Model
{
    protected $table = 'aggregats_synthese_mission_mensuelle';

    protected $fillable = [
        'mission_id',
        'annee',
        'mois',
        'dimes',
        'offrande_ecole_sabbat',
        'budget_eglise_locale',
        'fonds_missionnaires',
        'autres_offrandes',
        'montant_total',
        'calcule_le',
    ];

    protected function casts(): array
    {
        return [
            'dimes' => 'decimal:2',
            'offrande_ecole_sabbat' => 'decimal:2',
            'budget_eglise_locale' => 'decimal:2',
            'fonds_missionnaires' => 'decimal:2',
            'autres_offrandes' => 'decimal:2',
            'montant_total' => 'decimal:2',
            'calcule_le' => 'datetime',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }
}
