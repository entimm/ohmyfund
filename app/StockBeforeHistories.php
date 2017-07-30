<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockBeforeHistories extends Model
{
    protected $fillable = [
        'symbol',
        'open',
        'high',
        'low',
        'close',
        'volume',
        'lot_volume',
        'percent',
        'turnrate',
        'ma5',
        'ma10',
        'ma20',
        'ma30',
        'chg',
        'dif',
        'dea',
        'macd',
        'date',
    ];
}
