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

        foreach ($this->stocks() as $symbol) {
            $quotes = resolve('xueqiu')->resolveQuotes($symbol);
            $stock = Stock::firstOrNew(array_only($quotes, 'symbol'));
            $stock->code = $quotes['code'];
            $stock->name = $quotes['name'];
            $stock->data = array_except($quotes, ['symbol', 'code', 'name']);

            $span = $stock->counted_at ? 10 : 0;
            $this->process($symbol, 'normal', $span);
            $this->process($symbol, 'before', $span);

            $stock->counted_at = Carbon::now();

            $stock->save();
        }

        $this->info('update stock data done 😎');
    }

    protected function process($symbol, $type, $span)
    {
        $list = resolve('xueqiu')->resolveHistory($symbol, $type, $span);
        $list = array_reverse($list);
        $touchNum = 0;
        DB::transaction(function () use ($list, $symbol, &$touchNum, $type) {
            foreach ($list as $item) {
                $date = date('Y-m-d', $item['timestamp'] / 1000);
                $item['symbol'] = $symbol;
                $item['date'] = $date;
                $className = $type == 'normal' ? StockNormalHistories::class : StockBeforeHistories::class;
                $history = $className::firstOrCreate(['symbol' => $symbol, 'date' => $date], $item);
                if (! $history->wasRecentlyCreated) {
                    break;
                }
                $touchNum++;
            }
        });
        $this->info("{$symbol} | $type | {$touchNum}");
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
