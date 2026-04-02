<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EgliseLocale extends Model
{
    protected $table = 'eglises_locales';

    protected $fillable = [
        'mission_id',
        'district_id',
        'nom',
        'code_unique',
        'actif',
        'indicateurs_financiers',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
            'indicateurs_financiers' => 'array',
        ];
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function recapsSabbat(): HasMany
    {
        return $this->hasMany(RecapSabbatEglise::class, 'eglise_locale_id');
    }

    public function rapportsMensuels(): HasMany
    {
        return $this->hasMany(RapportMensuelEglise::class, 'eglise_locale_id');
    }

    public function rapportsMembres(): HasMany
    {
        return $this->hasMany(RapportMembreEglise::class, 'eglise_locale_id');
    }

    public function membres(): HasMany
    {
        return $this->hasMany(Membre::class, 'eglise_locale_id');
    }

    public function baptemes(): HasMany
    {
        return $this->hasMany(Bapteme::class, 'eglise_locale_id');
    }

    public function departementsMinisters(): HasMany
    {
        return $this->hasMany(DepartementMinistere::class, 'eglise_locale_id');
    }
}
