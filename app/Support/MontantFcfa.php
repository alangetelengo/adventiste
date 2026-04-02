<?php

namespace App\Support;

final class MontantFcfa
{
    /**
     * Extrait une valeur numérique exploitable par la validation Laravel (nullable).
     */
    public static function parseToNumericString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return self::normalizeFloatString((string) $value);
        }
        if (is_numeric($value)) {
            return self::normalizeFloatString((string) (0 + $value));
        }
        $s = preg_replace('/\s+/u', '', trim((string) $value));
        $s = preg_replace('/fcfa/iu', '', $s);
        $s = preg_replace('/[^\d.,-]/', '', $s ?? '');
        if ($s === '' || $s === '-' || $s === '.' || $s === ',') {
            return null;
        }
        $s = str_replace(',', '.', $s);
        if (! is_numeric($s)) {
            return null;
        }

        return self::normalizeFloatString($s);
    }

    /**
     * Affichage type « 10 000.00 fcfa ».
     */
    public static function formatDisplay(float|string|int|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        $n = (float) $value;
        $parts = explode('.', number_format($n, 2, '.', ''));
        $int = $parts[0];
        $dec = $parts[1] ?? '00';
        $intFormatted = preg_replace('/\B(?=(\d{3})+(?!\d))/', ' ', $int) ?? $int;

        return $intFormatted.'.'.$dec.' fcfa';
    }

    private static function normalizeFloatString(string $s): string
    {
        return number_format((float) $s, 2, '.', '');
    }
}
