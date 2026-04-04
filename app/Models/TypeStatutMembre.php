<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeStatutMembre extends Model
{
    public const CODE_ACTIF = 'actif';

    public const CODE_REGULIER = 'regulier';

    public const CODE_REFROIDI = 'refroidi';

    protected $table = 'types_statut_membres';

    protected $fillable = [
        'mission_id',
        'code',
        'libelle',
        'description',
        'couleur',
        'ordre',
        'actif',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'actif' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function membres(): HasMany
    {
        return $this->hasMany(Membre::class, 'type_statut_membre_id');
    }

    public function historiques(): HasMany
    {
        return $this->hasMany(MembreHistoriqueStatut::class, 'type_statut_membre_id');
    }

    /** @param Builder<self> $query */
    public function scopeActifsPourMission(Builder $query, int $missionId): Builder
    {
        return $query
            ->where('mission_id', $missionId)
            ->where('actif', true)
            ->orderBy('ordre')
            ->orderBy('libelle');
    }
}
