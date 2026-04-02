<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembreHistoriqueStatut extends Model
{
    protected $table = 'membre_historique_statuts';

    protected $fillable = [
        'membre_id',
        'type_statut_membre_id',
        'change_par_user_id',
        'motif',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'membre_id');
    }

    public function typeStatut(): BelongsTo
    {
        return $this->belongsTo(TypeStatutMembre::class, 'type_statut_membre_id');
    }

    public function changePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'change_par_user_id');
    }
}
