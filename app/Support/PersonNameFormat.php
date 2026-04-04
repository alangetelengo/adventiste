<?php

namespace App\Support;

/**
 * Convention d’affichage et de persistance : NOM en majuscules, prénom avec initiale(s) en majuscule.
 */
final class PersonNameFormat
{
    public static function nom(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        $v = trim($value);

        return $v === '' ? '' : mb_strtoupper($v, 'UTF-8');
    }

    /**
     * Première lettre de chaque segment (espaces, tirets, apostrophes courantes) en majuscule, le reste en minuscules.
     */
    public static function prenom(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        $v = mb_strtolower(trim($value), 'UTF-8');
        if ($v === '') {
            return '';
        }

        $formatted = preg_replace_callback(
            '/(^|[\s\-\'’])(\p{L})/u',
            static fn (array $m): string => $m[1].mb_strtoupper($m[2], 'UTF-8'),
            $v
        );

        return is_string($formatted) ? $formatted : $v;
    }
}
