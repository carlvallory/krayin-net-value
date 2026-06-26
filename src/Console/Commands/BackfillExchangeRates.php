<?php

namespace CarlVallory\KrayinNetValue\Console\Commands;

use CarlVallory\KrayinNetValue\Models\ExchangeRate;
use CarlVallory\KrayinNetValue\Services\Bcp\BcpRateFetcher;
use Illuminate\Console\Command;

class BackfillExchangeRates extends Command
{
    protected $signature = 'exchange-rates:backfill {year}';

    protected $description = 'Carga el histórico anual de cotizaciones USD/PYG del BCP en exchange_rates';

    public function handle(BcpRateFetcher $fetcher): int
    {
        $year  = (int) $this->argument('year');
        $rates = $fetcher->fetchYear($year);

        if (empty($rates)) {
            $this->warn("Sin tasas para {$year} (¿BCP no disponible?).");

            return self::SUCCESS;
        }

        foreach ($rates as $date => $rate) {
            ExchangeRate::updateOrCreate(
                ['date' => $date, 'currency_from' => 'USD', 'currency_to' => 'PYG'],
                ['rate' => $rate, 'source' => 'bcp']
            );
        }

        $this->info(count($rates) . " tasas cargadas para {$year}.");

        return self::SUCCESS;
    }
}
