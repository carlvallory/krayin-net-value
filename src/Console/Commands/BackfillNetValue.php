<?php

namespace CarlVallory\KrayinNetValue\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillNetValue extends Command
{
    protected $signature = 'leads:backfill-net-value';

    protected $description = 'Puebla net_value = lead_value en los leads históricos con net_value NULL (válido en modelo B2B sin deducción de comisión de pasarela: neto = bruto)';

    public function handle(): int
    {
        // En B2B sin deducción de comisión de pasarela, el plugin calcula
        // net_value = gross_value = lead_value (ver class-data-mapper.php).
        // Los leads sincronizados antes de existir el campo custom_net_value
        // quedaron con net_value NULL; acá se rellenan desde lead_value.
        // Idempotente: solo toca filas con net_value NULL y lead_value presente.
        $updated = DB::table('leads')
            ->whereNull('net_value')
            ->whereNotNull('lead_value')
            ->update(['net_value' => DB::raw('lead_value')]);

        $this->info("{$updated} leads con net_value poblado desde lead_value.");

        return self::SUCCESS;
    }
}
