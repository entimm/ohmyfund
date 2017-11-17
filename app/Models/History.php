<?php

namespace App\Models;

use App\Presenters\HistoryPresenter;
use Illuminate\Database\Eloquent\Model;
use McCool\LaravelAutoPresenter\HasPresenter;

/**
 * App\Models\History.
 *
 * @property int $id
 * @property string $code 基金代码
 * @property string $date 日期
 * @property int|null $unit 单位净值
 * @property int|null $total 累计净值
 * @property int|null $rate 日增长率
 * @property int|null $buy_status 申购状态
 * @property int|null $sell_status 赎回状态
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $bonus 分红
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereBonus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereBuyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereSellStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\History whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class History extends Model implements HasPresenter
{
    protected $fillable = ['code', 'date', 'unit', 'total', 'rate', 'buy_status', 'sell_status', 'bonus'];

    public static $buyStatusList = [
        1 => '开放申购',
        2 => '场内买入',
        3 => '限制大额申购',
        4 => '认购期',
        5 => '暂停申购',
        6 => '暂停交易',
        7 => '封闭期',
    ];

    public static $sellStatusList = [
        1 => '开放赎回',
        2 => '场内卖出',
        3 => '认购期',
        4 => '暂停赎回',
        5 => '暂停交易',
        6 => '封闭期',
    ];

    public function getPresenterClass()
    {
        return HistoryPresenter::class;
    }

    public function transform()
    {
        $data = [
            'date'  => $this->date,
            'unit'  => round($this->unit / 10000, 4),
            'total' => round($this->total / 10000, 4),
            'rate'  => round($this->rate / 10000, 4),
        ];
        if ($this->bonus) {
            $data['bonus'] = $this->bonus;
        }
        return $data;
    }

    /**
     * 保存基金历史记录，并返回操作数量.
     *
     * @param $records
     * @param $fundCode
     *
     * @return int
     */
    public function saveRecords($records, $fundCode)
    {
        // 开启事务，保证下面sql语句一起执行成功
        $touchNum = 0;
        DB::transaction(function () use ($records, $fundCode, &$touchNum) {
            foreach ($records as $key => $record) {
                $record['code'] = $fundCode;
                $history = History::firstOrNew(array_only($record, ['code', 'date']), $record);
                // 如果存在数据，那么就停止后续数据库操作
                if ($history->exists) {
                    break;
                }
                $history->save();
                $touchNum++;
            }
        });

        return $touchNum;
    }

    public function history($code, $begin, $end)
    {
        return $this->scopeQuery(function ($query) use ($code, $begin, $end) {
            return $query->select(['date', 'unit', 'rate', 'bonus'])
                ->where('code', $code)
                ->when($begin, function ($query) use ($begin) {
                    return $query->where('date', '>=', $begin);
                })->when($end, function ($query) use ($end) {
                    return $query->where('date', '<=', $end);
                })->orderBy('date', 'asc');
        })->all();
    }

    public function event($code, $begin, $end)
    {
        return $this->scopeQuery(function ($query) use ($code, $begin, $end) {
            return $query->select(['date', 'bonus'])
                ->where('code', $code)
                ->where('bonus', '<>', '')
                ->when($begin, function ($query) use ($begin) {
                    return $query->where('date', '>=', $begin);
                })->when($end, function ($query) use ($end) {
                    return $query->where('date', '<=', $end);
                })->orderBy('date', 'asc');
        })->skipPresenter()->all();
    }
}
