<?php

namespace App\Models;

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

    /** @return array<string, string> */
    public static function labelsTypes(): array
    {
        return [
            self::TYPE_IMMERSION => 'Immersion',
            self::TYPE_PROFESSION_FOI => 'Profession de foi',
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

