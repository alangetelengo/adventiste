<?php

namespace App\Models;

use App\Support\PersonNameFormat;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bapteme extends Model
{
    use HasFactory;

    public const TYPE_IMMERSION = 'immersion';

    public const TYPE_PROFESSION_FOI = 'profession_de_foi';

    protected $table = 'baptemes';

    protected $fillable = [
        'identifiant_public',
        'eglise_locale_id',
        'membre_id',
        'nom',
        'prenom',
        'type_bapteme',
        'date_bapteme',
        'lieu_bapteme',
        'officiant',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_bapteme' => 'date',
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
    public static function labelsTypes(): array
    {
        return [
            self::TYPE_IMMERSION => __('modules.baptemes.types.immersion'),
            self::TYPE_PROFESSION_FOI => __('modules.baptemes.types.profession_de_foi'),
        ];
    }

    public function egliseLocale(): BelongsTo
    {
        return $this->belongsTo(EgliseLocale::class, 'eglise_locale_id');
    }

    public function membre(): BelongsTo
    {
        return $this->belongsTo(Membre::class, 'membre_id');
    }
}
