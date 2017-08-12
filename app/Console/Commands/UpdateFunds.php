<?php

namespace App\Console\Commands;

use App\Models\Fund;
use Illuminate\Console\Command;
use App\Services\EastmoneyService;

class UpdateFunds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:funds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update funds';

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
        $this->info('update funds 🙏');
        $records = resolve(EastmoneyService::class)->requestFunds();

        $progressBar = $this->output->createProgressBar(count($records));
        $progressBar->setBarWidth(50);
        foreach ($records as $record) {
            Fund::firstOrCreate(['code' => $record['code']], $record);
            $progressBar->advance();
        }
        $progressBar->finish();
    }
}
