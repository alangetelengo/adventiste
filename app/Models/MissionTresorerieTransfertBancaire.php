<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionTresorerieTransfertBancaire extends Model
{
    protected $table = 'mission_tresorerie_transferts_bancaires';

    protected $fillable = [
        'mission_id',
        'annee',
        'mois',
        'montant_transfere',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'annee' => 'integer',
            'mois' => 'integer',
            'montant_transfere' => 'decimal:2',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }
}
