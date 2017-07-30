<?php

namespace App\Console\Commands;

use App\Fund;
use App\Services\EastmoneyService;
use Illuminate\Console\Command;

class UpdateRanks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:ranks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update ranks';

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
        $this->info('update ranks 🙏');
        $records = resolve(EastmoneyService::class)->ranks();

        $funds = Fund::get();
        $count = count($funds);
        foreach ($funds as $key => $fund) {
            if (isset($records[$fund->code])) {
                $record = $records[$fund->code];
                $fund->fill($record);
                $fund->save();
            }
            $processPercent = str_pad(round(($key + 1) * 100 / $count, 2).'%', 7, ' ', STR_PAD_LEFT);
            // 进度 | 基金代码 | 排行日期 | 基金成立日期
            $this->info("{$processPercent} | {$fund->code} | {$record['rank_date']} | {$record['born_date']}");
        }
        $this->info('update ranks done 😎');
    }
}
