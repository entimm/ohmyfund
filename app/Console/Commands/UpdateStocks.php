<?php

namespace App\Console\Commands;

use App\Models\Stock;
use App\Models\StockHistory;
use App\Services\XueQiuService;
use Carbon\Carbon;
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
     * @var StockHistory
     */
    private $stockHistory;

    /**
     * Create a new command instance.
     *
     * @param StockHistory $stockHistory
     */
    public function __construct(StockHistory $stockHistory)
    {
        parent::__construct();

        $this->stockHistory = $stockHistory;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('update stock data... 🙏');

        foreach (config('stocks') as $symbol) {
            $quotes = resolve(XueQiuService::class)->requestQuotes($symbol);
            $stock = Stock::firstOrNew(array_only($quotes, 'symbol'), $quotes);
            $stock->data = array_except($quotes, ['symbol', 'code', 'name']);

            $this->process($stock, StockHistory::NORMAL_TYPE);
            $this->process($stock, StockHistory::BEFORE_TYPE);

            $stock->counted_at = Carbon::now();

            $stock->save();
        }

        $this->info('update stock data done 😎');
    }

    /**
     * 获取前复权\未复权股票历史.
     *
     * @param \Illuminate\Database\Eloquent\Model $stock
     * @param $type
     */
    protected function process($stock, $type)
    {
        $symbol = $stock->symbol;
        $typeName = $type == StockHistory::NORMAL_TYPE ? 'normal' : 'before';
        $list = resolve(XueQiuService::class)->requestHistory($symbol, $typeName, $stock->counted_at ? $stock->counted_at->getTimestamp() : 0);
        $list = array_reverse($list);

        $touchNum = $this->stockHistory->saveRecords($list, $symbol, $type);
        $this->info("{$symbol} | {$typeName} | {$touchNum}");
    }
}
