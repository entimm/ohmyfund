<?php

namespace App\Console\Commands;

use App\Fund;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

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
        $url = 'http://fund.eastmoney.com/js/fundcode_search.js';
        $content = $response = $client->get($url)->getBody()->getContents();
        $beginPos = strpos($content, '[[');
        $json = substr($content, $beginPos, strlen($content) - $beginPos -1);
        $records = json_decode($json, true);
        foreach ($records as $record) {
            Fund::updateOrCreate(['code' => $record[0]], [
                'short_name' => $record[1],
                'name' => $record[2],
                'type' => array_flip(Fund::$types)[$record[3]],
                'pinyin_name' => $record[4],
            ]);
        }
    }
}
