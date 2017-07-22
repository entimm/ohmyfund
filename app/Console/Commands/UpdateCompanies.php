<?php

namespace App\Console\Commands;

use App\Company;
use App\Conpany;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

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
     *
     * @return void
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
    public function handle(Client $client)
    {
        $url = 'http://fund.eastmoney.com/js/jjjz_gs.js';
        $content = $client->get($url)->getBody()->getContents();
        $beginPos = strpos($content, '[[');
        $endPos = strpos($content, ']}');
        $json = substr($content, $beginPos, $endPos - $beginPos + 1);
        $records = json_decode($json, true);

        foreach ($records as $record) {
            Company::updateOrCreate(['code' => $record[0]], ['name' => $record[1]]);
        }
    }
}
