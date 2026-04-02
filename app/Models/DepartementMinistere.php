<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepartementMinistere extends Model
{
    protected $table = 'departements_ministeres';

    protected $fillable = [
        'identifiant_public',
        'eglise_locale_id',
        'nom',
        'code_unique',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'actif' => 'boolean',
        ];
    }

    public function egliseLocale(): BelongsTo
    {
        return $this->belongsTo(EgliseLocale::class, 'eglise_locale_id');
    }

    public function lignesRecap(): HasMany
    {
        return $this->hasMany(LigneDimeOffrandeRecap::class, 'departement_ministere_id');
    }
}
