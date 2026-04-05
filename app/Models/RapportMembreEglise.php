<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RapportMembreEglise extends Model
{
    public const TYPE_MENSUEL = 'mensuel';
    public const TYPE_ANNUEL = 'annuel';

    public const ETAT_BROUILLON = 'brouillon';
    public const ETAT_SOUMIS = 'soumis';
    public const ETAT_VALIDE = 'valide';
    public const ETAT_REJETE = 'rejete';

    protected $table = 'rapports_membres_eglise';

    protected $fillable = [
        'identifiant_public',
        'eglise_locale_id',
        'type_periode',
        'annee',
        'mois',
        'etat',
        'soumis_le',
        'soumis_par_user_id',
        'revu_le',
        'revu_par_user_id',
        'commentaire_mission',
        'total_membres',
        'total_baptemes_immersion',
        'total_baptemes_profession_foi',
        'total_entrees_transfert',
        'total_changements_statut',
        'stats_statuts',
        'notes_locales',
    ];

    protected function casts(): array
    {
        return [
            'soumis_le' => 'datetime',
            'revu_le' => 'datetime',
            'total_membres' => 'integer',
            'total_baptemes_immersion' => 'integer',
            'total_baptemes_profession_foi' => 'integer',
            'total_entrees_transfert' => 'integer',
            'total_changements_statut' => 'integer',
            'stats_statuts' => 'array',
        ];
    }

    /** @return array<string, string> */
    public static function labelsTypesPeriode(): array
    {
        return [
            self::TYPE_MENSUEL => __('secretariat.rapport_membre.type_mensuel'),
            self::TYPE_ANNUEL => __('secretariat.rapport_membre.type_annuel'),
        ];
    }

    /** @return array<string, string> */
    public static function labelsEtats(): array
    {
        return [
            self::ETAT_BROUILLON => __('secretariat.rapport_membre.etat_brouillon'),
            self::ETAT_SOUMIS => __('secretariat.rapport_membre.etat_soumis'),
            self::ETAT_VALIDE => __('secretariat.rapport_membre.etat_valide'),
            self::ETAT_REJETE => __('secretariat.rapport_membre.etat_rejete'),
        ];
    }

    public function egliseLocale(): BelongsTo
    {
        return $this->belongsTo(EgliseLocale::class, 'eglise_locale_id');
    }

    public function soumisPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'soumis_par_user_id');
    }

    public function revuPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revu_par_user_id');
    }
}
