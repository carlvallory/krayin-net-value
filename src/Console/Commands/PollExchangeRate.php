<?php

namespace CarlVallory\KrayinNetValue\Console\Commands;

use CarlVallory\KrayinNetValue\Models\ExchangeRate;
use CarlVallory\KrayinNetValue\Services\Bcp\BcpRateFetcher;
use Illuminate\Console\Command;

class PollExchangeRate extends Command
{
    protected $signature = 'exchange-rates:poll';

    protected $description = 'Consulta al BCP la última cotización USD/PYG y la guarda (resiliente, reintentable)';

    public function handle(BcpRateFetcher $fetcher): int
    {
        $latest = $fetcher->fetchLatest();

        if ($latest === null) {
            $this->warn('BCP sin datos en este momento; se reintentará en la próxima corrida.');

            return self::SUCCESS;
        }

        ExchangeRate::updateOrCreate(
            ['date' => $latest['date'], 'currency_from' => 'USD', 'currency_to' => 'PYG'],
            ['rate' => $latest['rate'], 'source' => 'bcp']
        );

        $this->info("Cotización {$latest['date']}: {$latest['rate']} guardada.");

        return self::SUCCESS;
    }
}
