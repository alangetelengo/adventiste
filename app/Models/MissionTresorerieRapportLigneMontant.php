<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionTresorerieRapportLigneMontant extends Model
{
    protected $table = 'mission_tresorerie_rapport_ligne_montants';

    protected $fillable = [
        'rapport_id',
        'ligne_id',
        'montant_mois',
        'montant_cumule',
    ];

    protected function casts(): array
    {
        return [
            'montant_mois' => 'decimal:2',
            'montant_cumule' => 'decimal:2',
        ];
    }

    public function rapport(): BelongsTo
    {
        return $this->belongsTo(MissionTresorerieRapportMensuel::class, 'rapport_id');
    }

    public function ligne(): BelongsTo
    {
        return $this->belongsTo(MissionTresorerieVentilationLigne::class, 'ligne_id');
    }
}
