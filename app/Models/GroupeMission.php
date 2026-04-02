<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupeMission extends Model
{
    protected $table = 'groupes_mission';

    protected $fillable = ['mission_id', 'nom', 'code_unique', 'actif'];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function entreesFinancieres(): HasMany
    {
        return $this->hasMany(EntreeFinanciereGroupeMission::class, 'groupe_mission_id');
    }

    public function membres(): HasMany
    {
        return $this->hasMany(Membre::class, 'groupe_mission_id');
    }
}
