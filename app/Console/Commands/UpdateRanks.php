<?php

namespace App\Console\Commands;

use App\Fund;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
        $url = 'http://fund.eastmoney.com/data/rankhandler.aspx?op=ph&dt=kf&ft=all&st=asc&pi=1&pn=20000';
        $content = $response = $client->get($url)->getBody()->getContents();
        $beginPos = strpos($content, '[');
        $endPos = strpos($content, ']');
        $json = substr($content, $beginPos, $endPos - $beginPos + 1);
        $records = [];
        $result = json_decode($json, true);
        foreach ($result as $item) {
            $item = explode(',', $item);
            $records[$item[0]] = [
                'rank_date' => $item[3] ?: null,
                'unit' => $item[4] * 10000,
                'total' => $item[5] * 10000,
                'rate' => $item[6] * 10000,
                'in_1week' => $item[7] * 10000,
                'in_1month' => $item[8] * 10000,
                'in_3month' => $item[9] * 10000,
                'in_6month' => $item[10] * 10000,
                'current_year' => $item[14] * 10000,
                'in_1year' => $item[11] * 10000,
                'in_2year' => $item[12] * 10000,
                'in_3year' => $item[13] * 10000,
                'in_5year' => $item[24] * 10000,
                'since_born' => $item[15] * 10000,
                'born_date' => $item[16] ?: null,
            ];
        }
        $funds = Fund::get();
        foreach ($funds as $fund) {
            if (!isset($records[$fund->code])) continue;
            $fund->fill($records[$fund->code]);
            $fund->save();
            $this->info("{$fund->code} | {$records[$fund->code]['rank_date']} | {$records[$fund->code]['born_date']}");
            unset($records[$fund->code]);
        }
        dd($records);
    }
}
