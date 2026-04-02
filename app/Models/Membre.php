<?php

namespace App\Models;

use Database\Factories\MembreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Membre extends Model
{
    /** @use HasFactory<MembreFactory> */
    use HasFactory;

    public const MODE_ENTREE_BAPTEME = 'bapteme';

    public const MODE_ENTREE_TRANSFERT = 'transfert';

    public const TYPE_BAPTEME_IMMERSION = 'immersion';

    public const TYPE_BAPTEME_PROFESSION_FOI = 'profession_de_foi';

    protected $table = 'membres';

    protected $fillable = [
        'identifiant_public',
        'eglise_locale_id',
        'groupe_mission_id',
        'type_statut_membre_id',
        'mode_entree',
        'type_bapteme_entree',
        'nom',
        'prenom',
        'sexe',
        'date_naissance',
        'lieu_naissance',
        'noms_pere',
        'noms_mere',
        'adresses',
        'telephone',
        'niveau_etudes',
        'occupation',
        'situation_matrimoniale',
        'date_mariage',
        'conjoint',
        'date_bapteme',
        'lieu_bapteme',
        'religion_anterieure',
        'recu_dans_eglise_de',
        'recu_le',
        'baptise_par',
        'observations',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_mariage' => 'date',
            'date_bapteme' => 'date',
            'recu_le' => 'date',
        ];
    }

    /** @return array<string, string> */
    public static function labelsModesEntree(): array
    {
        return [
            self::MODE_ENTREE_BAPTEME => 'Baptême',
            self::MODE_ENTREE_TRANSFERT => 'Transfert',
        ];
    }

    /** @return array<string, string> */
    public static function labelsTypesBaptemeEntree(): array
    {
        return [
            self::TYPE_BAPTEME_IMMERSION => 'Immersion',
            self::TYPE_BAPTEME_PROFESSION_FOI => 'Profession de foi',
        ];
    }

    public function egliseLocale(): BelongsTo
    {
        return $this->belongsTo(EgliseLocale::class, 'eglise_locale_id');
    }

    public function groupeMission(): BelongsTo
    {
        return $this->belongsTo(GroupeMission::class, 'groupe_mission_id');
    }

    public function baptemes(): HasMany
    {
        return $this->hasMany(Bapteme::class, 'membre_id');
    }

    public function typeStatut(): BelongsTo
    {
        return $this->belongsTo(TypeStatutMembre::class, 'type_statut_membre_id');
    }

    public function historiqueStatuts(): HasMany
    {
        return $this->hasMany(MembreHistoriqueStatut::class, 'membre_id')
            ->orderByDesc('changed_at')
            ->orderByDesc('id');
    }
}
