<?php

namespace CarlVallory\KrayinNetValue\Services\Bcp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BcpHttpRateFetcher implements BcpRateFetcher
{
    private const URL_ANUAL = 'https://www.bcp.gov.py/webapps/web/cotizacion/referencial-fluctuante/anual';

    public function fetchYear(int $year): array
    {
        // Campos reales del form #form-fecha del BCP: 'anho' (año) y 'tipoOperacion' (compra|venta).
        // Usamos 'venta' (la tasa con la que se valoriza el ingreso PYG a USD).
        $response = Http::asForm()->timeout(30)->post(self::URL_ANUAL, [
            'anho'          => $year,
            'tipoOperacion' => 'venta',
        ]);

        if (! $response->successful()) {
            Log::warning('BCP fetchYear falló', ['year' => $year, 'status' => $response->status()]);

            return [];
        }

        return $this->parseAnnualTable($response->body(), $year);
    }

    public function fetchLatest(): ?array
    {
        $year  = (int) date('Y');
        $rates = $this->fetchYear($year);

        if (empty($rates)) {
            return null;
        }

        $lastDate = array_key_last($rates);

        return ['date' => $lastDate, 'rate' => $rates[$lastDate]];
    }

    /** Tabla días(1-31) × meses(ENE..DIC) → ['YYYY-MM-DD'=>float] */
    private function parseAnnualTable(string $html, int $year): array
    {
        $rates = [];
        $dom   = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        // La planilla de datos es la <table id="cotizacion-interbancaria"> con filas día×mes.
        // (Hay otra tabla con el mismo id que solo trae el título; sus filas se descartan
        //  porque la celda de "día" no es un número válido 1-31.)
        $rows = $xpath->query("//table[contains(@id,'cotizacion')]//tr");

        foreach ($rows as $row) {
            $cells = $xpath->query('.//td|.//th', $row);
            if ($cells->length === 0) {
                continue;
            }

            $day = (int) trim($cells->item(0)->textContent);
            if ($day < 1 || $day > 31) {
                continue; // header (#, ENE..) o fila de título
            }

            for ($month = 1; $month <= 12; $month++) {
                $cell = $cells->item($month);
                if (! $cell) {
                    continue;
                }

                $value = BcpNumberParser::parse($cell->textContent);
                if ($value === null) {
                    continue; // ND o vacío
                }

                if (! checkdate($month, $day, $year)) {
                    continue;
                }

                $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $rates[$date] = $value;
            }
        }

        ksort($rates);

        return $rates;
    }
}
