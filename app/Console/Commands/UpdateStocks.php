<?php

namespace App\Console\Commands;

use App\Services\XueQiuService;
use App\Stock;
use App\StockBeforeHistories;
use App\StockNormalHistories;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class UpdateStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update stocks';

    /**
     * Create a new command instance.
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
        $this->info('update stock data... 🙏');

        $xueQiu = resolve(XueQiuService::class);
        $xueQiu->tryAuth(env('XUEQIU_USERNAME'), env('XUEQIU_PASSWORD'));
        foreach ($this->stocks() as $symbol) {
            $quotes = $xueQiu->resolveQuotes($symbol);
            $stock = Stock::firstOrNew(array_only($quotes, 'symbol'));
            $stock->code = $quotes['code'];
            $stock->name = $quotes['name'];
            $stock->data = array_except($quotes, ['symbol', 'code', 'name']);


            $span = $stock->profit_date ? 10 : 0;
            $list = $xueQiu->resolveHistory($symbol, 'normal', $span);
            $list = array_reverse($list);
            $touchNum = 0;
            DB::transaction(function () use ($list, $symbol, &$touchNum) {
                foreach ($list as $item) {
                    $date = date('Y-m-d', $item['timestamp'] / 1000);
                    $item['symbol'] = $symbol;
                    $item['date'] = $date;
                    $history = StockNormalHistories::firstOrCreate(['symbol' => $symbol, 'date' => $date], $item);
                    if (!$history->wasRecentlyCreated) {
                        break;
                    }
                    $touchNum++;
                }
            });
            $this->info("{$symbol} | normal | {$touchNum}");

            $list = $xueQiu->resolveHistory($symbol, 'before', $span);
            $list = array_reverse($list);
            $touchNum = 0;
            DB::transaction(function () use ($list, $symbol, &$touchNum) {
                foreach ($list as $item) {
                    $date = date('Y-m-d', $item['timestamp'] / 1000);
                    $item['symbol'] = $symbol;
                    $item['date'] = $date;
                    $history = StockBeforeHistories::firstOrCreate(['symbol' => $symbol, 'date' => $date], $item);
                    if (!$history->wasRecentlyCreated) {
                        break;
                    }
                    $touchNum++;
                }
            });
            $this->info("{$symbol} | before | {$touchNum}");


            $stock->profit_date = date('Y-m-d',$list[0]['timestamp'] / 1000);
            $stock->counted_at = Carbon::now();

            $stock->save();
        }

        $this->info('update stock data done 😎');
    }

    private function stocks()
    {
        return [
            'sh000001',
            'sh000002',
            'sh000003',
            'sh000016',

            'sz399001',
            'sz399005',
            'sz399006',
            'sz399300',

            'HKHSI',

            'QQQ',
            'SP500',
            'DJI30',
        ];
    }
}
