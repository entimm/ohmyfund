<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\History
 *
 * @mixin \Eloquent
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereBonus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereBuyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereSellStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\History whereUpdatedAt($value)
 */
class History extends Model
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
}
