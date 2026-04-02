<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneDimeOffrandeRecap extends Model
{
    public const TYPE_DIME = 'dime';

    public const TYPE_OFFRANDE = 'offrande';

    public const TYPE_DON = 'don';

    public const STATUT_BROUILLON = 'brouillon';

    public const STATUT_SOUMIS = 'soumis';

    public const STATUT_VERROUILLE = 'verrouille';

    public const STATUT_REJETE = 'rejete';

    public const ORIGINE_ASSEMBLEE = 'assemblee';

    public const ORIGINE_INDIVIDUEL = 'individuel';

    public const MODE_DON_ARGENT = 'argent';

    public const MODE_DON_NATURE = 'nature';

    public const DESTINATION_DON_LOCALE = 'locale';

    public const DESTINATION_DON_MISSION = 'mission';

    protected $table = 'lignes_dime_offrande_recap';

    protected $fillable = [
        'recap_sabbat_eglise_id',
        'type_recette_id',
        'membre_id',
        'departement_ministere_id',
        'nom_visiteur',
        'designation',
        'mode_don',
        'destination_don',
        'quantite_nature',
        'unite_nature',
        'origine',
        'type_revenu',
        'dimes',
        'offrandes',
        'ordre_ligne',
        'statut_ligne',
    ];

    protected function casts(): array
    {
        return [
            'dimes' => 'decimal:2',
            'offrandes' => 'decimal:2',
            'quantite_nature' => 'decimal:2',
        ];
    }

    public function recapSabbat(): BelongsTo
    {
        return $this->belongsTo(RecapSabbatEglise::class, 'recap_sabbat_eglise_id');
    }

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'membre_id');
    }

    public function typeRecette(): BelongsTo
    {
        return $this->belongsTo(TypeRecetteMission::class, 'type_recette_id');
    }

    public function departementMinistere(): BelongsTo
    {
        return $this->belongsTo(DepartementMinistere::class, 'departement_ministere_id');
    }

    public function estModifiableParTresorierEglise(): bool
    {
        return in_array($this->statut_ligne, [self::STATUT_BROUILLON, self::STATUT_REJETE], true);
    }

    /** @return array<string, string> */
    public static function libellesTypesRevenu(): array
    {
        return [
            self::TYPE_DIME => 'Dîme',
            self::TYPE_OFFRANDE => 'Offrande',
            self::TYPE_DON => 'Don',
        ];
    }

    /**
     * @return array{dimes: float, offrandes: float, type_revenu: string}
     */
    public static function repartirMontant(string $type, float $montant): array
    {
        return match ($type) {
            self::TYPE_DIME => ['dimes' => $montant, 'offrandes' => 0, 'type_revenu' => self::TYPE_DIME],
            self::TYPE_OFFRANDE => ['dimes' => 0, 'offrandes' => $montant, 'type_revenu' => self::TYPE_OFFRANDE],
            self::TYPE_DON => ['dimes' => 0, 'offrandes' => $montant, 'type_revenu' => self::TYPE_DON],
            default => ['dimes' => 0, 'offrandes' => 0, 'type_revenu' => self::TYPE_OFFRANDE],
        };
    }

    /**
     * @return array{dimes: float, offrandes: float, type_revenu: string}
     */
    public static function repartirDepuisCategorie(string $categorie, float $montant): array
    {
        $t = match ($categorie) {
            TypeRecetteMission::CATEGORIE_DIME => self::TYPE_DIME,
            TypeRecetteMission::CATEGORIE_DON => self::TYPE_DON,
            default => self::TYPE_OFFRANDE,
        };

        return self::repartirMontant($t, $montant);
    }

    /**
     * @return list<array{id: int|null, membre_id: int|string|null, nom_visiteur: string, type_recette_id: int|string, origine: string, montant: float|string, statut_ligne: string}>
     */
    public function versLignesFormulaire(): array
    {
        $this->loadMissing('typeRecette');

        $d = (float) $this->dimes;
        $o = (float) $this->offrandes;
        $membreId = $this->membre_id;
        $nomBrut = $this->nom_visiteur;
        $nom = is_string($nomBrut) ? $nomBrut : '';
        $montant = $d > 0 ? $d : $o;
        $typeId = $this->type_recette_id;

        return [[
            'id' => (int) $this->id,
            'membre_id' => $membreId,
            'nom_visiteur' => $nom,
            'type_recette_id' => $typeId ?? '',
            'origine' => $this->origine ?? self::ORIGINE_INDIVIDUEL,
            'montant' => $montant > 0 ? $montant : '',
            'designation' => $this->designation ?? '',
            'mode_don' => $this->mode_don ?? self::MODE_DON_ARGENT,
            'destination_don' => $this->destination_don ?? self::DESTINATION_DON_LOCALE,
            'quantite_nature' => $this->quantite_nature ?? '',
            'unite_nature' => $this->unite_nature ?? '',
            'statut_ligne' => $this->statut_ligne ?? self::STATUT_BROUILLON,
        ]];
    }
}
