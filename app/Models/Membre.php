<?php

namespace App\Models;

use App\Support\PersonNameFormat;
use Database\Factories\MembreFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
        'date_admission_eglise',
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
            'date_admission_eglise' => 'date',
            'actif' => 'boolean',
        ];
    }

    /** @return Attribute<string, string> */
    protected function nom(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => PersonNameFormat::nom($value),
            set: fn (?string $value) => PersonNameFormat::nom($value),
        );
    }

    /** @return Attribute<string, string> */
    protected function prenom(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => PersonNameFormat::prenom($value),
            set: fn (?string $value) => PersonNameFormat::prenom($value),
        );
    }

    /** @return array<string, string> */
    public static function labelsModesEntree(): array
    {
        return [
            self::MODE_ENTREE_BAPTEME => __('modules.membres.mode_entree.bapteme'),
            self::MODE_ENTREE_TRANSFERT => __('modules.membres.mode_entree.transfert'),
        ];
    }

    /** @return array<string, string> */
    public static function labelsTypesBaptemeEntree(): array
    {
        return [
            self::TYPE_BAPTEME_IMMERSION => __('modules.membres.type_bapteme_entree.immersion'),
            self::TYPE_BAPTEME_PROFESSION_FOI => __('modules.membres.type_bapteme_entree.profession_de_foi'),
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

    /**
     * Date d’entrée à l’église locale : priorité au registre d’admission, sinon « reçu le » (transfert) ou date de baptême.
     */
    public function resolveDateEntreeEglise(): ?Carbon
    {
        if ($this->date_admission_eglise !== null) {
            return $this->date_admission_eglise->copy();
        }

        $date = match ($this->mode_entree) {
            self::MODE_ENTREE_TRANSFERT => $this->recu_le,
            self::MODE_ENTREE_BAPTEME => $this->date_bapteme,
            default => $this->recu_le ?? $this->date_bapteme,
        };

        return ($date ?? $this->created_at)?->copy();
    }

    /** @param Builder<self> $query */
    public function scopeOrderByDateEntreeEgliseDesc(Builder $query): Builder
    {
        $t = self::MODE_ENTREE_TRANSFERT;
        $b = self::MODE_ENTREE_BAPTEME;
        $key = $query->getModel()->getQualifiedKeyName();

        return $query->orderByRaw(
            'COALESCE(date_admission_eglise, CASE WHEN mode_entree = ? THEN recu_le WHEN mode_entree = ? THEN date_bapteme ELSE COALESCE(recu_le, date_bapteme) END, created_at) DESC',
            [$t, $b]
        )->orderByDesc($key);
    }
}
