<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

/**
 * App\Entities\Fund.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int $type
 * @property string $short_name
 * @property string $pinyin_name
 * @property string|null $profit_date 收益日期
 * @property \Carbon\Carbon|null $counted_at 统计时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $status
 * @property int $unit
 * @property int $total
 * @property int $rate
 * @property int $in_1week
 * @property int $in_1month
 * @property int $in_3month
 * @property int $in_6month
 * @property int $current_year
 * @property int $in_1year
 * @property int $in_2year
 * @property int $in_3year
 * @property int $in_5year
 * @property int $since_born
 * @property string|null $born_date
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereBornDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereCountedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereCurrentYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereIn1month($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereIn1week($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereIn1year($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereIn2year($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereIn3month($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereIn3year($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereIn5year($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereIn6month($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund wherePinyinName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereProfitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereSinceBorn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Entities\Fund whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Fund extends Model implements Transformable
{
    use TransformableTrait;

    protected $fillable = ['code', 'name', 'type', 'short_name', 'pinyin_name',
        'unit', 'total', 'rate', 'in_1week', 'in_1month', 'in_3month', 'in_6month',
        'current_year', 'in_1year', 'in_2year', 'in_3year', 'in_5year', 'since_born', 'born_date', ];

    protected $dates = [
        'created_at',
        'updated_at',
        'counted_at',
    ];

    public static $statusList = [
        0, // 默认状态
        1, // 关注
        2, // 忽略
        3, // 空数据
        4, // 极少数据
        5, // 异常数据
    ];

    public static $types = [
      1 => '混合型',
      2 => '债券型',
      3 => '定开债券',
      4 => '联接基金',
      5 => '货币型',
      6 => '债券指数',
      7 => '保本型',
      8 => '理财型',
      9 => 'QDII',
      10 => '股票指数',
      11 => 'QDII-指数',
      12 => '股票型',
      13 => '固定收益',
      14 => '分级杠杆',
      15 => '其他创新',
      16 => 'ETF-场内',
      17 => 'QDII-ETF',
      18 => '债券创新-场内',
      19 => '封闭式',
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }
}
