<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RapportMensuelEglise extends Model
{
    public const ETAT_BROUILLON = 'brouillon';
    public const ETAT_SOUMIS = 'soumis';
    public const ETAT_VALIDE_MISSION = 'valide_mission';
    public const ETAT_REFUSE_MISSION = 'refuse_mission';

    protected $table = 'rapports_mensuels_eglise';

    protected $fillable = [
        'identifiant_public',
        'eglise_locale_id',
        'annee',
        'mois',
        'regenere_le',
        'verrouille_le',
        'total_dimes_mois',
        'total_moitie_offrandes_mois',
        'total_autres_offrandes_mission_mois',
        'total_a_transferer_mission_mois',
        'signe_tresorier',
        'date_signature_tresorier',
        'signe_pasteur',
        'date_signature_pasteur',
        'signe_secretaire',
        'date_signature_secretaire',
        'pieces_jointes_signatures',
        'etabli_a',
        'etabli_le',
        'etat_transmission',
        'soumis_le',
        'soumis_par_user_id',
        'mission_revu_le',
        'mission_revu_par_user_id',
        'mission_commentaire',
    ];

    protected function casts(): array
    {
        return [
            'regenere_le' => 'datetime',
            'verrouille_le' => 'datetime',
            'total_dimes_mois' => 'decimal:2',
            'total_moitie_offrandes_mois' => 'decimal:2',
            'total_autres_offrandes_mission_mois' => 'decimal:2',
            'total_a_transferer_mission_mois' => 'decimal:2',
            'signe_tresorier' => 'boolean',
            'signe_pasteur' => 'boolean',
            'signe_secretaire' => 'boolean',
            'date_signature_tresorier' => 'date',
            'date_signature_pasteur' => 'date',
            'date_signature_secretaire' => 'date',
            'pieces_jointes_signatures' => 'array',
            'etabli_le' => 'date',
            'soumis_le' => 'datetime',
            'mission_revu_le' => 'datetime',
        ];
    }

    /** @return array<string, string> */
    public static function labelsEtatsTransmission(): array
    {
        return [
            self::ETAT_BROUILLON => 'Brouillon',
            self::ETAT_SOUMIS => 'Soumis à la mission',
            self::ETAT_VALIDE_MISSION => 'Validé mission',
            self::ETAT_REFUSE_MISSION => 'Refusé mission',
        ];
    }

    public function egliseLocale(): BelongsTo
    {
        return $this->belongsTo(EgliseLocale::class, 'eglise_locale_id');
    }

    public function lignesSabbat(): HasMany
    {
        return $this->hasMany(RapportMensuelLigneSabbat::class, 'rapport_mensuel_eglise_id');
    }

    public function lignesSynthese(): HasMany
    {
        return $this->hasMany(LigneSyntheseMensuelleEglise::class, 'rapport_mensuel_eglise_id');
    }

    public function soumisPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'soumis_par_user_id');
    }

    public function missionRevuPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mission_revu_par_user_id');
    }
}
