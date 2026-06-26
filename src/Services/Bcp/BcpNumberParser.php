<?php

namespace CarlVallory\KrayinNetValue\Services\Bcp;

class BcpNumberParser
{
    /** "6.719,39" → 6719.39 ; "ND"/"" → null */
    public static function parse(string $raw): ?float
    {
        $raw = trim($raw);

        if ($raw === '' || strtoupper($raw) === 'ND') {
            return null;
        }

        $normalized = str_replace(['.', ','], ['', '.'], $raw);

        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
