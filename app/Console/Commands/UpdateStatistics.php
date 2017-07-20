<?php

namespace App\Console\Commands;

use App\Fund;
use App\Statistic;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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
        $funds = Fund::where('count_date', '<', date('Y-m-d'))
            ->whereNotIn('status', [3, 4]) // 过滤没有数据和极少数据的基金
            ->whereNotIn('type', [5]) // 过滤货币基金
            ->get();
        foreach ($funds as $fund) {
            $this->updateOneFund($fund);
        }
    }

    protected function updateOneFund($fund)
    {
        // 通过 count_date 判断这只基金是否有被处理过
        $per = $fund->count_date ? self::BUFFER_DAY : self::INFINITE_DAY;
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
        foreach ($rows as $k => $row) {
            $elements = explode('</td>', $row);
            $elements = array_filter($elements);
            $recored = [];
            foreach ($elements as $kk => $element) {
                // 处理日期
                if ($kk == 0) {
                    preg_match('/\d{4}-\d{2}-\d{2}/', $element, $matches);
                    $recored[] = $matches[0];
                    continue;
                }

                $arr = explode('>', $element);
                $recored[] = end($arr);
            }
            if ($k == 0) {
                $fund->count_date = $recored[0];
            }
            $recoreds[] = $recored;
        }
        $this->info("{$fund->count_date} | {$fund->code} | {$totalRecords}");
        // 验证数据是否解析有误
        if ($per == self::INFINITE_DAY && $totalRecords != count($recoreds)) {
            $this->error("{$totalRecords} <> ".count($recoreds));
        }

        $rate = 0;
        foreach ($recoreds as $recored) {
            // 处理盈亏率
            if ($recored[3]) {
                $rate = substr($recored[3], 0, strlen($recored[3]) - 1);
                if (is_numeric($rate)) {
                    $rate = floatval($recored[3]) * 10000;
                } else {
                    $this->error("rate is invalid!!!");
                    dd($recored);
                }
            }

            $statistic = Statistic::updateOrCreate([
                'code' => $fund->code,
                'date' => $recored[0],
            ], [
                'unit' => $recored[1] * 10000,
                'total' => $recored[2] * 10000,
                'rate' => $rate,
                'buy_status' => $recored[4],
                'sell_status' => $recored[5],
            ]);
            // 如果是更新到了就数据，那么就停止后续数据库操作
            if ($statistic->wasRecentlyCreated) break;
        }
        // 标记10天内都没数据的基金
        $diffDay = date_diff(date_create($fund->count_date), date_create())->days;
        if ($diffDay > self::BUFFER_DAY) {
            $fund->status = 4;
        }
        $fund->save();
    }
}
