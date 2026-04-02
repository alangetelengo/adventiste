<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneSyntheseMensuelleEglise extends Model
{
    protected $table = 'lignes_synthese_mensuelle_eglise';

    protected $fillable = [
        'rapport_mensuel_eglise_id',
        'indice_sabbat',
        'date_sabbat',
        'dimes',
        'offrande_ecole_sabbat',
        'budget_eglise_locale',
        'fonds_missionnaires',
        'autres_offrandes',
        'montant_total',
    ];

    protected function casts(): array
    {
        return [
            'date_sabbat' => 'date',
            'dimes' => 'decimal:2',
            'offrande_ecole_sabbat' => 'decimal:2',
            'budget_eglise_locale' => 'decimal:2',
            'fonds_missionnaires' => 'decimal:2',
            'autres_offrandes' => 'decimal:2',
            'montant_total' => 'decimal:2',
        ];
    }

    public function rapportMensuel(): BelongsTo
    {
        return $this->belongsTo(RapportMensuelEglise::class, 'rapport_mensuel_eglise_id');
    }
}
