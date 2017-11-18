<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StockHistory.
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereChg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereClose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereDea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereDif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereHigh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereLotVolume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereLow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereMa10($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereMa20($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereMa30($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereMa5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereMacd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory wherePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereTurnrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\StockHistory whereVolume($value)
 * @mixin \Eloquent
 */
class StockHistory extends Model
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

    /**
     * 保存基金历史记录，并返回操作数量.
     *
     * @param $list
     * @param $symbol
     * @param $type
     *
     * @return int
     */
    public function saveRecords($list, $symbol, $type)
    {
        // 开启事务，保证下面sql语句一起执行成功
        $touchNum = 0;
        DB::transaction(function () use ($list, $symbol, $type, &$touchNum) {
            foreach ($list as $item) {
                $date = date('Y-m-d', $item['timestamp'] / 1000);
                $uniqueKeys = [
                    'symbol' => $symbol,
                    'date'   => $date,
                    'type'   => $type,
                ];
                $history = StockHistory::firstOrCreate($uniqueKeys, $item);
                if (!$history->wasRecentlyCreated) {
                    break;
                }
                $touchNum++;
            }
        });

        return $touchNum;
    }

    /**
     * 蜡烛图数据.
     *
     * @param $symbol
     * @param $type
     * @param $begin
     * @param $end
     */
    public function candlestick($symbol, $type, $begin, $end)
    {
        return $this->select([
                'open',
                'high',
                'low',
                'close',
                'volume',
                'date',
            ])->where('symbol', $symbol)
            ->where('type', $type)
            ->when($begin, function ($query) use ($begin) {
                return $query->where('date', '>=', $begin);
            })->when($end, function ($query) use ($end) {
                return $query->where('date', '<=', $end);
            })->orderBy('date', 'asc')
            ->get();
    }

    /**
     * 收盘数据.
     *
     * @param $symbol
     * @param $type
     * @param $begin
     * @param $end
     *
     * @return mixed
     */
    public function values($symbol, $type, $begin, $end)
    {
        return $this->select(['close as value', 'date'])
            ->where('symbol', $symbol)
            ->where('type', $type)
            ->when($begin, function ($query) use ($begin) {
                return $query->where('date', '>=', $begin);
            })->when($end, function ($query) use ($end) {
                return $query->where('date', '<=', $end);
            })->orderBy('date', 'asc')
            ->get();
    }
}
