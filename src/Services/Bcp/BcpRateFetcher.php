<?php

namespace CarlVallory\KrayinNetValue\Services\Bcp;

interface BcpRateFetcher
{
    /** @return array<string,float> mapa 'YYYY-MM-DD' => tasa, solo días con cotización */
    public function fetchYear(int $year): array;

    /** @return array{date:string,rate:float}|null última cotización disponible */
    public function fetchLatest(): ?array;
}
