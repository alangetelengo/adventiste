<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mission extends Model
{
    protected $table = 'missions';

    protected $fillable = ['nom', 'nom_court'];

    protected static function booted(): void
    {
        static::created(function (Mission $mission): void {
            TypeRecetteMission::seedDefaultsForMission((int) $mission->id);
        });
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class, 'mission_id');
    }

    public function eglisesLocales(): HasMany
    {
        return $this->hasMany(EgliseLocale::class, 'mission_id');
    }

    public function groupesMission(): HasMany
    {
        return $this->hasMany(GroupeMission::class, 'mission_id');
    }

    public function aggregatsSyntheseMensuelle(): HasMany
    {
        return $this->hasMany(AggregatSyntheseMissionMensuelle::class, 'mission_id');
    }

    public function reglesVentilationRecettes(): HasOne
    {
        return $this->hasOne(MissionReglesVentilationRecettes::class, 'mission_id');
    }

    public function tresorerieVentilationLignes(): HasMany
    {
        return $this->hasMany(MissionTresorerieVentilationLigne::class, 'mission_id')->orderBy('ordre');
    }

    public function tresorerieRapportsMensuels(): HasMany
    {
        return $this->hasMany(MissionTresorerieRapportMensuel::class, 'mission_id');
    }

    public function typesRecette(): HasMany
    {
        return $this->hasMany(TypeRecetteMission::class, 'mission_id')->orderBy('ordre');
    }
}
