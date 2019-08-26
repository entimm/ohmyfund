<?php

namespace App\Services;

use App\Exceptions\NonDataException;
use App\Exceptions\ResolveErrorException;
use App\Exceptions\ValidateException;
use App\Models\Fund;
use App\Models\History;
use App\Traits\HttpRequest;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Table2Array;

class EastmoneyService
{
    use HttpRequest;

    /**
     * 数据拉取数据量限制.
     */
    const INFINITE_DAY = 10000;

    /**
     * request fund companies
     *
     * @return mixed
     */
    public function requestCompanies()
    {
        $url = 'http://fund.eastmoney.com/js/jjjz_gs.js';
        $content = $this->get($url);

        $beginPos = strpos($content, '[[');
        $endPos = strpos($content, ']}');
        $json = substr($content, $beginPos, $endPos - $beginPos + 1);
        $records = json_decode($json, true);
        array_walk($records, function ($record) {
            return [
                'code' => $record[0],
                'name' => $record[1],
            ];
        });

        return $records;
    }

    /**
     * 获取基金基本数据.
     *
     * @return mixed
     */
    public function requestFunds()
    {
        $url = 'http://fund.eastmoney.com/js/fundcode_search.js';
        $content = $this->get($url);

        $beginPos = strpos($content, '[[');
        $json = substr($content, $beginPos, strlen($content) - $beginPos - 1);
        $records = json_decode($json, true);
        array_walk($records, function (&$record) {
            $record = array_combine(['code', 'short_name', 'name', 'type', 'pinyin_name'], $record);
            $record['type'] = array_flip(Fund::$types)[$record['type']];
        });

        return $records;
    }

    /**
     * 获取基金排名.
     *
     * @return array
     */
    public function requestRanks()
    {
        $url = 'http://fund.eastmoney.com/data/rankhandler.aspx?op=ph&dt=kf&ft=all&st=asc&pi=1&pn=20000';
        $content = $this->get($url);

        $beginPos = strpos($content, '[');
        $endPos = strpos($content, ']');
        $json = substr($content, $beginPos, $endPos - $beginPos + 1);
        $records = json_decode($json, true);

        $records = Collection::make($records)->mapWithKeys(function ($item) {
            $item = explode(',', $item);
            return [
                $item[0] => [
                    'rank_date'    => $item[3] ?: null,
                    'unit'         => ($item[4] ?: 0) * 10000,
                    'total'        => ($item[5] ?: 0) * 10000,
                    'rate'         => ($item[6] ?: 0) * 10000,
                    'in_1week'     => ($item[7] ?: 0) * 10000,
                    'in_1month'    => ($item[8] ?: 0) * 10000,
                    'in_3month'    => ($item[9] ?: 0) * 10000,
                    'in_6month'    => ($item[10] ?: 0) * 10000,
                    'current_year' => ($item[14] ?: 0) * 10000,
                    'in_1year'     => ($item[11] ?: 0) * 10000,
                    'in_2year'     => ($item[12] ?: 0) * 10000,
                    'in_3year'     => ($item[13] ?: 0) * 10000,
                    'in_5year'     => ($item[24] ?: 0) * 10000,
                    'since_born'   => ($item[15] ?: 0) * 10000,
                    'born_date'    => $item[16] ?: null,
                ],
            ];
        });

        return $records->toArray();
    }

    /**
     * 获取基金净值历史.
     *
     * @param $fundCode
     * @param $fundCountedAt
     *
     * @throws NonDataException
     * @throws ResolveErrorException
     * @throws ValidateException
     *
     * @return array
     */
    public function requestHistories($fundCode, $sdate, $edate)
    {
        $url = "http://fund.eastmoney.com/f10/F10DataApi.aspx?type=lsjz&code={$fundCode}&sdate={$sdate}&edate={$edate}&per=49";
        $content = $this->get($url);

        preg_match('/records:(\d+)/', $content, $matches);
        $totalRecord = $matches[1];
        if (!$totalRecord) {
            // throw new NonDataException();
        }

        preg_match('/pages:(\d+)/', $content, $matches);
        $pages = $matches[1];
        if ($pages > 1) {
            dd('page = '.$pages);
        }

        // 解析行记录
        $rows = Table2Array::convert($content);

        // 处理数据,从第一期开始
        $records = [];
        foreach (array_reverse($rows) as $k => $row) {
            try {
                $record = $this->resolveHistoryRecord($row, $records);
            } catch (\Exception $e) {
                throw new ResolveErrorException($e->getMessage(), $row);
            }

            array_unshift($records, $record);
        }

        return $records;
    }

    /**
     * 解析基金净值记录.
     *
     * @param $row
     * @param $records
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function resolveHistoryRecord($row, $records)
    {
        if (count($row) < 7) {
            if (count($row) == 1) {
                throw new \Exception(reset($row));
            }
            throw new \Exception('记录格式异常');
        }
        // key转换
        $keyMaps = [
            'date' => '净值日期',
            'unit' => '单位净值',
            'total' => '累计净值',
            'rate' => '日增长率',
            'buy_status' => '申购状态',
            'sell_status' => '赎回状态',
            'bonus' => '分红送配',
        ];
        foreach ($keyMaps as $key => $value) {
            $row[$key] = $row[$value];
            unset($row[$value]);
        }

        // 处理单条数据的每一个字段
        $last = $records[0] ?? null;
        // 处理日期,这个比较特殊，要特殊处理
        preg_match('/\d{4}-\d{2}-\d{2}/', $row['date'], $matches);
        $row['date'] = $matches[0];

        // 处理单位净值、累计净值,如果为空就取之前的值
        $row['unit'] = $row['unit'] ? $row['unit'] * 10000 : ($last['unit'] ?: 0);
        $row['total'] = $row['total'] ? $row['total'] * 10000 : ($last['total'] ?: 0);

        /*
         * 处理盈亏率
         * 1. 匹配数值去掉模板的百分号
         * 2. 处理空值，这时尝试自己计算盈亏率
         */
        $row['rate'] = $row['rate'] ? substr($row['rate'], 0, strlen($row['rate']) - 1) : null;
        if (is_null($row['rate'])) {
            $row['rate'] = $last['unit'] ? ($row['unit'] / $last['unit'] - 1) * 100 : 0;
        }
        $row['rate'] *= 10000;

        // 转换申购状态
        $row['buy_status'] = $row['buy_status'] ? array_search($row['buy_status'], History::$buyStatusList) : 0;
        if ($row['buy_status'] === false) {
            throw new \Exception('未知申购状态');
        }

        // 转换赎回状态
        $row['sell_status'] = $row['sell_status'] ? array_search($row['sell_status'], History::$sellStatusList) : 0;
        if ($row['sell_status'] === false) {
            throw new \Exception('未知赎回状态');
        }

        return $row;
    }

    /**
     * 获取基金估值并缓存起来.
     *
     * @param $fundCode
     * @param $noCache
     *
     * @return array
     */
    public function resolveEvaluateAndCache($fundCode, $noCache = false)
    {
        $key = 'evaluate_'.$fundCode;
        if ($noCache) {
            $evaluate = $this->requestEvaluate($fundCode);
            Cache::put($key, $evaluate, 5);
        } else {
            $evaluate = Cache::remember($key, 5, function () use ($fundCode) {
                return $this->requestEvaluate($fundCode);
            });
        }

        return $evaluate;
    }

    /**
     * 获取基金估值
     *
     * @param $fundCode
     *
     * @return array
     */
    protected function requestEvaluate($fundCode)
    {
        $microTime = microtime();
        $url = "http://fundgz.1234567.com.cn/js/{$fundCode}.js?rt={$microTime}";
        $content = $this->get($url);

        $beginPos = strpos($content, '{');
        $json = substr($content, $beginPos, -2);
        $result = json_decode($json, true);
        $data = [
            'code'   => $fundCode,
            'name'   => $result['name'],
            'date'   => $result['jzrq'],
            'origin' => $result['dwjz'],
            'value'  => $result['gsz'],
            'rate'   => $result['gszzl'],
            'time'   => $result['gztime'],
        ];

        return $data;
    }
}
