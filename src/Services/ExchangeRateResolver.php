<?php

namespace CarlVallory\KrayinNetValue\Services;

use Carbon\Carbon;
use CarlVallory\KrayinNetValue\Models\ExchangeRate;

class ExchangeRateResolver
{
    /**
     * Tasa de cierre USD/PYG de la fecha dada, o la de la última fecha
     * disponible anterior (último día hábil previo). null si no hay ninguna.
     */
    public function rateForDate(string|Carbon $date): ?float
    {
        $target = Carbon::parse($date)->toDateString();

        $row = ExchangeRate::query()
            ->where('currency_from', 'USD')
            ->where('currency_to', 'PYG')
            ->where('date', '<=', $target)
            ->orderByDesc('date')
            ->first();

        return $row ? (float) $row->rate : null;
    }
}
