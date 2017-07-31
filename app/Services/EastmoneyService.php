<?php

namespace App\Services;

use App\Exceptions\NonDataException;
use App\Exceptions\ResolveErrorException;
use App\Exceptions\ValidateException;
use App\Statistic;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class EastmoneyService
{
    /**
     * 数据拉取数据量限制.
     */
    const BUFFER_DAY = 10;
    const INFINITE_DAY = 10000;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function companies()
    {
        $url = 'http://fund.eastmoney.com/js/jjjz_gs.js';
        $content = $this->client->get($url)->getBody()->getContents();
        $beginPos = strpos($content, '[[');
        $endPos = strpos($content, ']}');
        $json = substr($content, $beginPos, $endPos - $beginPos + 1);
        $records = json_decode($json, true);

        return $records;
    }

    public function funds()
    {
        $url = 'http://fund.eastmoney.com/js/fundcode_search.js';
        $content = $this->client->get($url)->getBody()->getContents();
        $beginPos = strpos($content, '[[');
        $json = substr($content, $beginPos, strlen($content) - $beginPos - 1);
        $records = json_decode($json, true);

        return $records;
    }

    public function ranks()
    {
        $url = 'http://fund.eastmoney.com/data/rankhandler.aspx?op=ph&dt=kf&ft=all&st=asc&pi=1&pn=20000';
        $content = $this->client->get($url)->getBody()->getContents();
        $beginPos = strpos($content, '[');
        $endPos = strpos($content, ']');
        $json = substr($content, $beginPos, $endPos - $beginPos + 1);
        $result = json_decode($json, true);

        $records = [];
        foreach ($result as $item) {
            $item = explode(',', $item);
            $records[$item[0]] = [
                'rank_date' => $item[3] ?: null,
                'unit' => $item[4] * 10000,
                'total' => $item[5] * 10000,
                'rate' => $item[6] * 10000,
                'in_1week' => $item[7] * 10000,
                'in_1month' => $item[8] * 10000,
                'in_3month' => $item[9] * 10000,
                'in_6month' => $item[10] * 10000,
                'current_year' => $item[14] * 10000,
                'in_1year' => $item[11] * 10000,
                'in_2year' => $item[12] * 10000,
                'in_3year' => $item[13] * 10000,
                'in_5year' => $item[24] * 10000,
                'since_born' => $item[15] * 10000,
                'born_date' => $item[16] ?: null,
            ];
        }

        return $records;
    }

    public function statistic($fundCode, $local = true)
    {
        $per = $local ? self::BUFFER_DAY : self::INFINITE_DAY;
        // 如果网络异常就不断间隔重试
        do {
            static $tryTimes = 0;
            $retry = false;
            try {
                $url = "http://fund.eastmoney.com/f10/F10DataApi.aspx?type=lsjz&code={$fundCode}&page=1&per={$per}";
                $content = $this->client->get($url)->getBody()->getContents();
            } catch (\Exception $e) {
                $tryTimes++;
                Log::error($e->getMessage(), [
                    'fund_code' => $fundCode,
                    'try_times' => $tryTimes,
                ]);
                sleep(10);
                $retry = true;
            }
        } while ($retry);

        preg_match('/records:(\d+)/', $content, $matches);
        $totalRecord = $matches[1];
        if (! $totalRecord) {
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
                $record = $this->resolveStatisticRecord($elements, $records);
            } catch (\Exception $e) {
                throw new ResolveErrorException($e->getMessage(), $row);
            }

            array_unshift($records, $record);
        }

        // 验证数据是否解析有误
        if ($per == self::INFINITE_DAY && $totalRecord != count($records)) {
            throw new ValidateException("数据自我验证失败：{$totalRecord} <> ".count($records));
        }

        return $records;
    }

    protected function resolveStatisticRecord($elements, $records)
    {
        $record = [];
        if (count($elements) < 6) {
            throw new \Exception('记录格式异常');
        }
        // 处理单条数据的每一个字段
        foreach ($elements as $kk => $element) {
            // 处理日期,这个比较特殊，要特殊处理
            if ($kk == 0) {
                preg_match('/\d{4}-\d{2}-\d{2}/', $element, $matches);
                $record[] = $matches[0];
                continue;
            }

            // 分割获取后续字段
            $value = explode('>', $element);
            $value = end($value);

            if (in_array($kk, [1, 2])) {
                // 处理单位净值、累计净值,如果为空就取之前的值
                $value = $value ? $value * 10000 : (isset($records[0]) ? $records[0][$kk] : 0);
            } elseif ($kk == 3) {
                /*
                 * 处理盈亏率
                 * 1. 匹配数值去掉模板的百分号
                 * 2. 处理空值，这时尝试自己计算盈亏率
                 */
                $value = $value ? substr($value, 0, strlen($value) - 1) : null;
                if (is_null($value)) {
                    $value = isset($records[0]) ? ($record[1] / $records[0][1] - 1) * 100 : 0;
                }
                $value *= 10000;
            } elseif ($kk == 4) {
                // 转换申购状态
                $value = $value ? array_search($value, Statistic::$buyStatusList) : 0;
                if ($value === false) {
                    throw new \Exception('未知申购状态');
                }
            } elseif ($kk == 5) {
                // 转换赎回状态
                $value = $value ? array_search($value, Statistic::$sellStatusList) : 0;
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
            $record[] = $value;
        }

        return $record;
    }

    public function evaluate($fundCodes = [])
    {
        foreach ($fundCodes as $code) {
            $data = $this->evaluateOne($code);
        }
    }

    protected function evaluateOne($fundCode)
    {
        $microTime = microtime();
        $url = "http://fundgz.1234567.com.cn/js/{$fundCode}.js?rt={$microTime}";
        $content = $this->client->get($url)->getBody()->getContents();
        $beginPos = strpos($content, '{');
        $json = substr($content, $beginPos, -2);
        $result = json_decode($json, true);
        $data = [
            'code' => $result['fundcode'],
            'name' => $result['name'],
            'date' => $result['jzrq'],
            'origin' => $result['dwjz'],
            'value' => $result['gsz'],
            'rate' => $result['gszzl'],
            'time' => $result['gztime'],
        ];

        return $data;
    }
}
