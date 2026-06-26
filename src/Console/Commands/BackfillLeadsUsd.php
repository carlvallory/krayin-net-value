<?php

namespace CarlVallory\KrayinNetValue\Console\Commands;

use CarlVallory\KrayinNetValue\Services\ExchangeRateResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillLeadsUsd extends Command
{
    protected $signature = 'leads:backfill-usd {year}';

    protected $description = 'Recalcula usd_rate/total_usd de los leads ganados del año usando la tasa de cierre del día del pedido';

    public function handle(ExchangeRateResolver $resolver): int
    {
        $year = (int) $this->argument('year');

        $leads = DB::table('leads')
            ->join('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->where('lead_pipeline_stages.code', 'won')
            ->whereYear('leads.created_at', $year)
            ->whereNotNull('leads.net_value')
            ->select('leads.id', 'leads.net_value', 'leads.created_at')
            ->get();

        $converted = 0;

        foreach ($leads as $lead) {
            $rate = $resolver->rateForDate($lead->created_at);
            if ($rate === null || (float) $rate == 0.0) {
                continue; // sin tasa hasta esa fecha; se reintenta en otra corrida
            }

            DB::table('leads')->where('id', $lead->id)->update([
                'usd_rate'  => $rate,
                'total_usd' => round(((float) $lead->net_value) / $rate, 4),
            ]);
            $converted++;
        }

        $this->info("{$converted} leads convertidos a USD para {$year}.");

        return self::SUCCESS;
    }
}
