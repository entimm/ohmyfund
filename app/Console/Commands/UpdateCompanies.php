<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\EastmoneyService;
use Illuminate\Console\Command;

class UpdateCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update companies';

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
        $records = resolve(EastmoneyService::class)->requestCompanies();
        foreach ($records as $record) {
            Company::updateOrCreate(['code' => $record[0]], ['name' => $record[1]]);
        }
        $this->info('update companies done 😎');
    }
}
