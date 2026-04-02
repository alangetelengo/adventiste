<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RapportMensuelLigneSabbat extends Model
{
    protected $table = 'rapports_mensuels_lignes_sabbat';

    protected $fillable = [
        'rapport_mensuel_eglise_id',
        'indice_sabbat_dans_mois',
        'date_sabbat',
        'total_dimes',
        'moitie_offrandes',
        'autres_offrandes_mission',
        'total_transferer_mission',
    ];

    protected function casts(): array
    {
        return [
            'date_sabbat' => 'date',
            'total_dimes' => 'decimal:2',
            'moitie_offrandes' => 'decimal:2',
            'autres_offrandes_mission' => 'decimal:2',
            'total_transferer_mission' => 'decimal:2',
        ];
    }

    public function rapportMensuel(): BelongsTo
    {
        return $this->belongsTo(RapportMensuelEglise::class, 'rapport_mensuel_eglise_id');
    }
}
