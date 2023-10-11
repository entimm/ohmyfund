<?php

namespace App\Models;

use App\Presenters\FundPresenter;
use App\Services\EastmoneyService;
use Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use McCool\LaravelAutoPresenter\HasPresenter;

/**
 * App\Models\Fund.
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereBornDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereCountedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereCurrentYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereIn1month($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereIn1week($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereIn1year($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereIn2year($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereIn3month($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereIn3year($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereIn5year($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereIn6month($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund wherePinyinName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereProfitDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereSinceBorn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Fund whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Fund extends Model implements HasPresenter
{
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
      1  => '混合型',
      2  => '债券型',
      3  => '定开债券',
      4  => '联接基金',
      5  => '货币型',
      6  => '债券指数',
      7  => '保本型',
      8  => '理财型',
      9  => 'QDII',
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
      20 => '混合-FOF',
      21 => '股票创新-场内',
      22 => '混合型-灵活',
      23 => '债券型-可转债',
      24 => '债券型-长债',
      25 => '混合型-偏股',
      26 => '指数型-股票',
      27 => '债券型-混合债',
      28 => '债券型-中短债',
      29 => '混合型-偏债',
      30 => '商品（不含QDII）',
      31 => '混合-绝对收益',
      32 => '混合型-平衡',
      33 => 'FOF',
      34 => 'Reits',
      35 => '',
    ];

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function getPresenterClass()
    {
        return FundPresenter::class;
    }

    public function getHistoriesAttribute()
    {
        $key = 'histories_'.$this->code;
        $histories = Cache::remember($key, 60, function () {
            $histories = History::select(['date', 'unit', 'rate', 'bonus', 'total'])
                ->where('code', $this->code)
                ->orderBy('date', 'desc')
                ->take(100)
                ->get()
                ->reverse()
                ->values();
            foreach ($histories as $history) {
                $history->unit = round($history->unit / 10000, 4);
                $history->total = round($history->total / 10000, 4);
                $history->rate = round($history->rate / 10000, 2);
            }

            return $histories;
        });

        return $histories;
    }

    public function getEvaluateRateAttribute()
    {
        $key = 'evaluate_'.$this->code;

        $evaluate = Cache::get($key);

        return $evaluate ? $evaluate['rate'] : '—';
    }

    public function getEvaluateTimeAttribute()
    {
        $key = 'evaluate_'.$this->code;

        $evaluate = Cache::get($key);

        return $evaluate ? $evaluate['time'] : '—';
    }

    /**
     * 获取即将更新的基金集合.
     */
    public function toUpdates()
    {
        return static::where(function ($query) {
            // 过滤掉今天结算过的
            $query->where('profit_date', '<', date('Y-m-d'))
                ->orWhereNull('profit_date');
        })->where(function ($query) {
            // 60分钟内更新过的不在更新
            $query->where('counted_at', '<', Carbon::now()->subMinutes(300))
                ->orWhereNull('counted_at');
        })->whereNotIn('status', [3, 4, 5]) // 过滤没有数据和极少数据
        ->whereNotIn('type', [5, 8]) // 过滤货币基金、理财型基金
        ->get();
    }

    /**
     * 获取将作显示的基金集合.
     */
    public function toShows($orderBy, $sortedBy)
    {
        return $this->whereNotIn('status', [3, 4, 5])
          ->whereNotIn('type', [5, 8])
          ->take(500)
          ->orderBy($orderBy, $sortedBy)
          ->paginate(20);
    }

}
