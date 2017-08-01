<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    protected $fillable = ['code', 'name', 'type', 'short_name', 'pinyin_name',
        'unit', 'total', 'rate', 'in_1week', 'in_1month', 'in_3month', 'in_6month',
        'current_year', 'in_1year', 'in_2year', 'in_3year', 'in_5year', 'since_born', 'born_date', ];

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
