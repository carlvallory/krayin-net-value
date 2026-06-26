<?php

namespace CarlVallory\KrayinNetValue\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';

    protected $fillable = ['date', 'currency_from', 'currency_to', 'rate', 'source'];

    protected $casts = [
        'date' => 'date',
        'rate' => 'decimal:4',
    ];
}
