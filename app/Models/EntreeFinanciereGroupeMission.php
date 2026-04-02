<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntreeFinanciereGroupeMission extends Model
{
    protected $table = 'entrees_financieres_groupe_mission';

    protected $fillable = [
        'identifiant_public',
        'groupe_mission_id',
        'annee',
        'mois',
        'dimes',
        'offrande_ecole_sabbat',
        'offrande_budget_eglise',
        'offrande_fonds_mission',
        'offrande_autres',
    ];

    protected function casts(): array
    {
        return [
            'dimes' => 'decimal:2',
            'offrande_ecole_sabbat' => 'decimal:2',
            'offrande_budget_eglise' => 'decimal:2',
            'offrande_fonds_mission' => 'decimal:2',
            'offrande_autres' => 'decimal:2',
        ];
    }

    public function groupeMission(): BelongsTo
    {
        return $this->belongsTo(GroupeMission::class, 'groupe_mission_id');
    }
}
