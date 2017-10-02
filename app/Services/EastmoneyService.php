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
    public function requestHistories($fundCode, $fundCountedAt)
    {
        $pageSize = self::INFINITE_DAY;
        if ($fundCountedAt) {
            $pageSize = Carbon::now()->diffInDays($fundCountedAt) + 1;
        }
        $url = "http://fund.eastmoney.com/f10/F10DataApi.aspx?type=lsjz&code={$fundCode}&page=1&per={$pageSize}";
        $content = $this->get($url);

        preg_match('/records:(\d+)/', $content, $matches);
        $totalRecord = $matches[1];
        if (!$totalRecord) {
            throw new NonDataException();
        }

        // 解析行记录
        $beginPos = strpos($content, '<tbody>') + strlen('<tbody>');
        $endPos = strpos($content, '</tbody>');
        $table = substr($content, $beginPos, $endPos - $beginPos);
        $rows = explode('</tr>', $table);
        $rows = array_filter($rows);

        // 处理数据,从第一期开始
        $records = [];
        foreach (array_reverse($rows) as $k => $row) {
            $elements = explode('</td>', $row);
            $elements = array_filter($elements);

            try {
                $record = $this->resolveHistoryRecord($elements, $records);
            } catch (\Exception $e) {
                throw new ResolveErrorException($e->getMessage(), $row);
            }

            array_unshift($records, $record);
        }

        // 验证数据是否解析有误
        if ($pageSize == self::INFINITE_DAY && $totalRecord != count($records)) {
            throw new ValidateException("数据自我验证失败：{$totalRecord} <> ".count($records));
        }

        return $records;
    }

    /**
     * 解析基金净值记录.
     *
     * @param $elements
     * @param $records
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function resolveHistoryRecord($elements, $records)
    {
        $record = [];
        if (count($elements) < 6) {
            throw new \Exception('记录格式异常');
        }
        $keyMaps = [
            'date',
            'unit',
            'total',
            'rate',
            'buy_status',
            'sell_status',
            'bonus',
        ];
        // 处理单条数据的每一个字段
        foreach ($elements as $kk => $element) {
            $last = $records[0] ?? null;
            $key = $keyMaps[$kk];
            // 处理日期,这个比较特殊，要特殊处理
            if ($kk == 0) {
                preg_match('/\d{4}-\d{2}-\d{2}/', $element, $matches);
                $record[$key] = $matches[0];
                continue;
            }

            // 分割获取后续字段
            $value = explode('>', $element);
            $value = end($value);

            if (in_array($kk, [1, 2])) {
                // 处理单位净值、累计净值,如果为空就取之前的值
                $value = $value ? $value * 10000 : ($last[$kk] ?: 0);
            } elseif ($kk == 3) {
                /*
                 * 处理盈亏率
                 * 1. 匹配数值去掉模板的百分号
                 * 2. 处理空值，这时尝试自己计算盈亏率
                 */
                $value = $value ? substr($value, 0, strlen($value) - 1) : null;
                if (is_null($value)) {
                    $value = $last ? ($record['unit'] / $last['unit'] - 1) * 100 : 0;
                }
                $value *= 10000;
            } elseif ($kk == 4) {
                // 转换申购状态
                $value = $value ? array_search($value, History::$buyStatusList) : 0;
                if ($value === false) {
                    throw new \Exception('未知申购状态');
                }
            } elseif ($kk == 5) {
                // 转换赎回状态
                $value = $value ? array_search($value, History::$sellStatusList) : 0;
                if ($value === false) {
                    throw new \Exception('未知赎回状态');
                }
            } elseif ($kk == 6) {
                // 处理分红
                if ($value && preg_match('/每份派现金(\d*\.\d*)元/', $value, $matches)) {
                    $value = $matches[1] * 10000;
                } else {
                    $value = 0;
                }
            }
            $record[$key] = $value;
        }

        return $record;
    }

    /**
     * 获取基金估值并缓存起来.
     *
     * @param $fundCode
     * @param $force
     *
     * @return array
     */
    public function resolveEvaluateAndCache($fundCode, $force = false)
    {
        $key = 'evaluate_'.$fundCode;
        if ($force) {
            $evaluate = $this->requestEvaluate($fundCode);
            Cache::put($key, $evaluate, 30);
        } else {
            $evaluate = Cache::remember($key, 30, function () use ($fundCode) {
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
