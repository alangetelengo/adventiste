<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeRecetteMission extends Model
{
    public const CATEGORIE_DIME = 'dime';

    public const CATEGORIE_OFFRANDE = 'offrande';

    public const CATEGORIE_DON = 'don';

    protected $table = 'types_recette_mission';

    protected $fillable = [
        'mission_id',
        'code',
        'libelle',
        'categorie',
        'ordre',
        'actif',
        'exclure_rapport_mission',
        'mission_sans_partage',
    ];

    protected function casts(): array
    {
        return [
            'ordre' => 'integer',
            'actif' => 'boolean',
            'exclure_rapport_mission' => 'boolean',
            'mission_sans_partage' => 'boolean',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function lignesRecap(): HasMany
    {
        return $this->hasMany(LigneDimeOffrandeRecap::class, 'type_recette_id');
    }

    /** @param  Builder<self>  $query */
    public function scopeActifsPourMission(Builder $query, int $missionId): Builder
    {
        return $query->where('mission_id', $missionId)->where('actif', true)->orderBy('ordre');
    }

    public static function seedDefaultsForMission(int $missionId): void
    {
        if (self::query()->where('mission_id', $missionId)->exists()) {
            return;
        }

        $now = now();
        $rows = [
            ['code' => 'dime', 'libelle' => 'Dîme', 'categorie' => self::CATEGORIE_DIME, 'ordre' => 10],
            ['code' => 'offrande_cultuelle', 'libelle' => 'Offrande cultuelle', 'categorie' => self::CATEGORIE_OFFRANDE, 'ordre' => 20],
            ['code' => 'don', 'libelle' => 'Don', 'categorie' => self::CATEGORIE_DON, 'ordre' => 30],
        ];
        foreach ($rows as $row) {
            self::query()->create(array_merge($row, ['mission_id' => $missionId, 'actif' => true]));
        }
    }

    /** @return array<string, string> */
    public static function libellesCategories(): array
    {
        return [
            self::CATEGORIE_DIME => 'Dîme (ventilation dîme)',
            self::CATEGORIE_OFFRANDE => 'Offrande (ventilation offrande)',
            self::CATEGORIE_DON => 'Don (ventilation don)',
        ];
    }
}
