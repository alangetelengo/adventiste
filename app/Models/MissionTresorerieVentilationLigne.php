<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionTresorerieVentilationLigne extends Model
{
    public const KIND_TITRE = 'titre';

    public const KIND_POURCENTAGE_DIMES = 'pourcentage_dimes';

    public const KIND_POURCENTAGE_OFFRANDES = 'pourcentage_offrandes';

    public const KIND_SOMME_CODES = 'somme_codes';

    protected $table = 'mission_tresorerie_ventilation_lignes';

    protected $fillable = [
        'mission_id',
        'code',
        'designation',
        'kind',
        'pourcentage',
        'somme_codes',
        'ordre',
    ];

    protected function casts(): array
    {
        return [
            'pourcentage' => 'decimal:4',
            'somme_codes' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function estTitre(): bool
    {
        return $this->kind === self::KIND_TITRE;
    }
}
