<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockHistories extends Model
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
        'type',
        'date',
    ];

    public static $stocks = [
        'sh000001',
        'sh000002',
        'sh000003',
        'sh000016',

        'sz399001',
        'sz399005',
        'sz399006',
        'sz399300',

        'HKHSI',

        'QQQ',
        'SP500',
        'DJI30',
    ];

    const NORMAL_TYPE = 1;
    const BEFORE_TYPE = 2;
}
