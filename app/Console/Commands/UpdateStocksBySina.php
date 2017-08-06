<?php

namespace App\Console\Commands;

use App\Services\SinaService;
use App\StockHistories;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/*
 * @deprecated
 */
class UpdateStocksBySina extends Command
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
        $this->info('update stocks 🙏');

        // $this->handleCn();
        $this->handleUs();

        $this->info('update stocks done 😎');
    }

    protected function handleCn()
    {
        foreach ($this->cnStocks() as $stock) {
            $touchNum = 0;
            $stock = $stock[0].$stock[1];
            $records = resolve(SinaService::class)->resolveCn($stock);
            DB::transaction(function () use ($records, $stock, &$touchNum) {
                foreach ($records as $key => $record) {
                    $record = $this->transformCn($record);
                    StockHistories::firstOrCreate(['code' => $stock, 'date' => $record['date']], $record);
                    $touchNum++;
                }
            });

            $this->info("{$stock} | {$touchNum}");
        }
    }

    private function cnStocks()
    {
        return [
            ['sh', '000001'],
            ['sh', '000002'],
            ['sh', '000003'],
            ['sh', '000016'],

            ['sz', '399001'],
            ['sz', '399005'],
            ['sz', '399006'],
            ['sz', '399300'],
        ];
    }

    private function transformCn($record)
    {
        $record['date'] = $record['day'];
        $record['open'] *= 1000;
        $record['high'] *= 1000;
        $record['low'] *= 1000;
        $record['close'] *= 1000;

        return $record;
    }

    protected function handleUs()
    {
        foreach ($this->usStocks() as $stock) {
            $touchNum = 0;
            $records = resolve(SinaService::class)->resolveUs($stock);
            DB::transaction(function () use ($records, $stock, &$touchNum) {
                foreach ($records as $key => $record) {
                    $record = $this->transformUs($record);
                    StockHistories::firstOrCreate(['code' => $stock, 'date' => $record['date']], $record);
                    $touchNum++;
                }
            });

            $this->info("{$stock} | {$touchNum}");
        }
    }

    private function transformUs($record)
    {
        $record['date'] = $record['d'];
        $record['open'] = $record['o'] * 1000;
        $record['high'] = $record['h'] * 1000;
        $record['low'] = $record['l'] * 1000;
        $record['close'] = $record['c'] * 1000;
        $record['volume'] = $record['v'];

        return $record;
    }

    private function usStocks()
    {
        return ['ixic', 'dji', 'inx'];
    }
}
