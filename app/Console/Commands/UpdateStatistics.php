<?php

namespace App\Console\Commands;

use App\Fund;
use App\Statistic;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:statistic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update statistic';

    /**
     * 数据拉取数据量限制.
     */
    const BUFFER_DAY = 10;
    const INFINITE_DAY = 10000;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('update statistic 🙏');
        $funds = Fund::where(function ($query) {
            // 过滤掉今天结算过的
            $query->where('profit_date', '<', date('Y-m-d'))
                ->orWhereNull('profit_date');
        })->where(function ($query) {
            // 60分钟内更新过的不在更新
            $query->where('counted_at', '<', Carbon::now()->subMinutes(60))
                ->orWhereNull('counted_at');
        })->whereNotIn('status', [3, 4, 5]) // 过滤没有数据和极少数据、有异常的基金
            ->whereNotIn('type', [5, 8]) // 过滤货币基金、理财型基金
            ->get();
        $count = count($funds);
        foreach ($funds as $key => $fund) {
            $touchNum = $this->updateOneFund($fund);
            // 进度百分数
            $processPercent = str_pad(round(($key + 1) * 100 / $count, 2).'%', 7, ' ', STR_PAD_LEFT);
            // 进度 | 最新收益日期 | 基金代码 | 更新条数
            $this->info("😃{$processPercent} | {$fund->profit_date} | {$fund->code} | {$touchNum}");
            $fund->save();
        }
        $this->info('update statistic done 😎');
    }

    protected function updateOneFund($fund)
    {
        // 通过 profit_date 判断这只基金是否有被处理过
        $per = $fund->profit_date ? self::BUFFER_DAY : self::INFINITE_DAY;
        $url = "http://fund.eastmoney.com/f10/F10DataApi.aspx?type=lsjz&code={$fund->code}&page=1&per={$per}";
        // 如果网络异常就不断间隔重试
        do {
            static $tryTimes = 0;
            $retry = false;
            try {
                $content = resolve(Client::class)->get($url)->getBody()->getContents();
            } catch (\Exception $e) {
                $tryTimes++;
                Log::error($e->getMessage(), [
                    'fund_code' => $fund->code,
                    'try_times' => $tryTimes,
                ]);
                sleep(10);
                $retry = true;
            }
        } while ($retry);

        preg_match('/records:(\d+)/', $content, $matches);
        $totalRecord = $matches[1];
        if (! $totalRecord) {
            // 如果没有历史就进行标记
            $fund->status = 3;

            return 0;
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
                $record = $this->resolveRecord($elements, $records);
            } catch (\Exception $e) {
                Log::error($e->getMessage(), [
                    'fund_code' => $fund->code,
                    'row' => $row,
                ]);
                $fund->status = 5;

                return 0;
            }
            array_unshift($records, $record);
        }
        $fund->profit_date = reset($records)[0] ?: null;

        // 验证数据是否解析有误
        if ($per == self::INFINITE_DAY && $totalRecord != count($records)) {
            Log::error('未通过数据验证', [
                'fund_code' => $fund->code,
                'total_record' => $totalRecord,
                'resolve_record' => count($records),
            ]);
            $this->error("{$totalRecord} <> ".count($records));
            $fund->status = 5;

            return 0;
        }
        // 开启事务，保证下面sql语句一起执行成功
        $touchNum = 0;
        DB::transaction(function () use ($records, $fund, &$touchNum) {
            foreach ($records as $key => $record) {
                $statistic = Statistic::firstOrNew([
                    'code' => $fund->code,
                    'date' => $record[0],
                ], [
                    'unit' => $record[1],
                    'total' => $record[2],
                    'rate' => $record[3],
                    'buy_status' => $record[4],
                    'sell_status' => $record[5],
                    'bonus' => $record[6],
                ]);
                // 如果存在数据，那么就停止后续数据库操作
                if ($statistic->exists) {
                    break;
                }
                $statistic->save();
                $touchNum++;
            }
        });
        // 标记10天内都没数据的基金
        $diffDay = date_diff(date_create($fund->profit_date), date_create())->days;
        if ($diffDay > self::BUFFER_DAY) {
            $fund->status = 4;
        }
        $fund->counted_at = Carbon::now();

        return $touchNum;
    }

    protected function resolveRecord($elements, $records)
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
}
