<?php

namespace App\Console\Commands;

use App\Stock;
use App\StockHistories;
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
            $this->process($symbol, StockHistories::NORMAL_TYPE, $span);
            $this->process($symbol, StockHistories::BEFORE_TYPE, $span);

            $stock->counted_at = Carbon::now();

            $stock->save();
        }

        $this->info('update stock data done 😎');
    }

    protected function process($symbol, $type, $span)
    {
        $typeName = $type == StockHistories::NORMAL_TYPE ? 'normal' : 'before';
        $list = resolve('xueqiu')->resolveHistory($symbol, $typeName, $span);
        $list = array_reverse($list);
        $touchNum = 0;
        DB::transaction(function () use ($list, $symbol, &$touchNum, $type) {
            foreach ($list as $item) {
                $date = date('Y-m-d', $item['timestamp'] / 1000);
                $item['symbol'] = $symbol;
                $item['date'] = $date;
                $item['type'] = $type;
                $history = StockHistories::firstOrCreate(['symbol' => $symbol, 'date' => $date], $item);
                if (! $history->wasRecentlyCreated) {
                    break;
                }
                $touchNum++;
            }
        });

        $this->info("{$symbol} | $typeName | {$touchNum}");
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
