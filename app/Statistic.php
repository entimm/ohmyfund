<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $fillable = ['code', 'date', 'unit','total', 'rate', 'buy_status', 'sell_status'];

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
