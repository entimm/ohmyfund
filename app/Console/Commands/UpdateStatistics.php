<?php

namespace App\Console\Commands;

use App\Fund;
use App\Statistic;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    const BUFFER_DAY = 10;
    const INFINITE_DAY = 10000;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $funds = Fund::where(function ($query) {
            // 过滤掉今天结算过的
            $query->where('profit_date', '<', date('Y-m-d'))
                ->orWhereNull('profit_date');
        })->where(function ($query) {
            // 60分钟内更新过的不在更新
            $query->where('counted_at', '<', Carbon::now()->subMinutes(60))
                ->orWhereNull('counted_at');
        })->whereNotIn('status', [3, 4]) // 过滤没有数据和极少数据的基金
            ->whereNotIn('type', [5, 8]) // 过滤货币基金、理财型基金
            ->get();
        foreach ($funds as $fund) {
            $this->updateOneFund($fund);
        }
    }

    protected function updateOneFund($fund)
    {
        // 通过 profit_date 判断这只基金是否有被处理过
        $per = $fund->profit_date ? self::BUFFER_DAY : self::INFINITE_DAY;
        $url = "http://fund.eastmoney.com/f10/F10DataApi.aspx?type=lsjz&code={$fund->code}&page=1&per={$per}";
        do {
            $retry = false;
            try {
                $content = resolve(Client::class)->get($url)->getBody()->getContents();
            } catch (\Exception $e) {
                dump($e);
                sleep(10);
                $retry = true;
            }
        } while($retry);
        preg_match('/records:(\d+)/', $content, $matches);
        $totalRecords = $matches[1];
        if (!$totalRecords) {
            $fund->status = 3;
            $this->warn("{$fund->code} has records {$totalRecords}");
            $fund->save();
            return;
        }

        $beginPos = strpos($content, '<tbody>') + strlen('<tbody>');
        $endPos = strpos($content, '</tbody>');
        $table = substr($content, $beginPos, $endPos - $beginPos);
        $rows = explode('</tr>', $table);
        $rows = array_filter($rows);

        $recoreds = [];
        // 处理数据
        foreach (array_reverse($rows) as $k => $row) {
            $elements = explode('</td>', $row);
            $elements = array_filter($elements);
            $recored = [];
            // 处理单条数据的每一个字段
            foreach ($elements as $kk => $element) {
                // 处理日期,这个比较特殊，要特殊处理
                if ($kk == 0) {
                    preg_match('/\d{4}-\d{2}-\d{2}/', $element, $matches);
                    $recored[] = $matches[0];
                    continue;
                }

                // 分割获取后续字段
                $value = explode('>', $element);
                $value = end($value);

                if (in_array($kk, [1, 2])) {
                    // 处理单位净值、累计净值
                    $value = $value ? $value * 10000 : (isset($recoreds[0]) ? $recoreds[0][$kk] : 0);
                } elseif ($kk == 3) {
                    /*
                     * 处理盈亏率
                     * 1. 匹配数值去掉模板的百分号
                     * 2. 处理空值
                     */
                    $value = $value ? substr($value, 0, strlen($value) - 1) : null;
                    if (is_null($value)) {
                        $value = isset($recoreds[0]) ? ($recored[1] / $recoreds[0][1] - 1) * 100 : 0;
                    }
                    $value *= 10000;
                } elseif ($kk == 4) {
                    // 转换申购状态
                    $value = $value ? array_search($value, Statistic::$buyStatusList) : 0;
                    if ($value === false) dd($fund->code, 4, $recored, $element);
                } elseif ($kk == 5) {
                    // 转换赎回状态
                    $value = $value ? array_search($value, Statistic::$sellStatusList) : 0;
                    if ($value === false) dd($fund->code, 5, $recored, $element);
                } elseif ($kk == 6) {
                    // 处理分红
                    if ($value && preg_match('/每份派现金(\d*\.\d*)元/', $value, $matches)) {
                        $value = $matches[1] * 10000;
                    } else {
                        $value = 0;
                    }
                }
                $recored[] = $value;
            }
            array_unshift($recoreds, $recored);
        }
        $fund->profit_date = reset($recoreds)[0] ?: null;
        $this->info("{$fund->profit_date} | {$fund->code} | {$totalRecords}");

        // 验证数据是否解析有误
        if ($per == self::INFINITE_DAY && $totalRecords != count($recoreds)) {
            $this->error("{$totalRecords} <> ".count($recoreds));
        }
        // 开启事务，保证下面sql语句一起执行成功
        DB::transaction(function () use ($recoreds, $fund) {
            foreach ($recoreds as $key => $recored) {
                $statistic = Statistic::updateOrCreate([
                    'code' => $fund->code,
                    'date' => $recored[0],
                ], [
                    'unit' => $recored[1],
                    'total' => $recored[2],
                    'rate' => $recored[3],
                    'buy_status' => $recored[4],
                    'sell_status' => $recored[5],
                    'bonus' => $recored[6],
                ]);
                // 如果是更新到了就数据，那么就停止后续数据库操作
                if (!$statistic->wasRecentlyCreated) break;
            }
        });
        // 标记10天内都没数据的基金
        $diffDay = date_diff(date_create($fund->profit_date), date_create())->days;
        if ($diffDay > self::BUFFER_DAY) {
            $fund->status = 4;
        }
        $fund->counted_at = Carbon::now();
        $fund->save();
    }
}
