<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Models\StockHistories.
 *
 * @property int $id
 * @property string $symbol
 * @property float $open
 * @property float $high
 * @property float $low
 * @property float $close
 * @property int $volume
 * @property int $lot_volume
 * @property float|null $percent
 * @property float|null $turnrate
 * @property float|null $ma5
 * @property float|null $ma10
 * @property float|null $ma20
 * @property float|null $ma30
 * @property float|null $chg
 * @property float|null $dif
 * @property float|null $dea
 * @property float|null $macd
 * @property int $type
 * @property string $date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereChg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereClose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereDea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereDif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereHigh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereLotVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereMa10($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereMa20($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereMa30($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereMa5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereMacd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereTurnrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistories whereVolume($value)
 * @mixin \Eloquent
 */
class StockHistories extends Model implements Transformable
{
    use TransformableTrait;

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
