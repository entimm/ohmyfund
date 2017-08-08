<?php

namespace App\Console\Commands;

use App\Repositories\StockHistoryRepository;
use App\Services\XueQiuService;
use App\Entities\Stock;
use App\Entities\StockHistories;
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
     * @var StockHistoryRepository
     */
    private $stockHistoryRepository;


    /**
     * Create a new command instance.
     *
     * @param StockHistoryRepository $stockHistoryRepository
     */
    public function __construct(StockHistoryRepository $stockHistoryRepository)
    {
        parent::__construct();

        $this->stockHistoryRepository = $stockHistoryRepository;
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
            $stock = Stock::firstOrNew(array_only($quotes, 'symbol'));
            $stock->code = $quotes['code'];
            $stock->name = $quotes['name'];
            $stock->data = array_except($quotes, ['symbol', 'code', 'name']);

            $this->process($stock, StockHistories::NORMAL_TYPE);
            $this->process($stock, StockHistories::BEFORE_TYPE);

            $stock->counted_at = Carbon::now();

            $stock->save();
        }

        $this->info('update stock data done 😎');
    }


    /**
     * 获取前复权\未复权股票历史
     *
     * @param \Illuminate\Database\Eloquent\Model $stock
     * @param $type
     */
    protected function process($stock, $type)
    {
        $symbol = $stock->symbol;
        $typeName = $type == StockHistories::NORMAL_TYPE ? 'normal' : 'before';
        $list = resolve(XueQiuService::class)->requestHistory($symbol, $typeName, $stock->counted_at ? $stock->counted_at->getTimestamp() : 0);
        $list = array_reverse($list);

        $touchNum = $this->stockHistoryRepository->saveRecords($list, $symbol, $type);
        $this->info("{$symbol} | {$typeName} | {$touchNum}");
    }
}
