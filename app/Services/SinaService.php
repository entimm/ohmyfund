<?php

namespace App\Services;

use GuzzleHttp\Client;

class SinaService
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function resolveCn($stock)
    {
        $url = "http://money.finance.sina.com.cn/quotes_service/api/json_v2.php/CN_MarketData.getKLineData?symbol={$stock}&scale=240&datalen=100000";
        $content = $this->client->get($url)->getBody()->getContents();
        $json = $this->makeJson($content);
        $records = $json ? json_decode($json, true) : [];

        return $records;
    }

    public function resolveUs($stock)
    {
        $url = "http://stock.finance.sina.com.cn/usstock/api/jsonp_v2.php/var%20_{$stock}=/US_MinKService.getDailyK?symbol=.{$stock}";
        $content = $this->client->get($url)->getBody()->getContents();
        $beginPos = strpos($content, '[');
        $content = substr($content, $beginPos, -2);
        $json = $this->makeJson($content);
        $records = $json ? json_decode($json, true) : [];

        return $records;
    }

    private function makeJson($content)
    {
        if (preg_match('/\w:/', $content)) {
            $json = preg_replace('/(\w+):/is', '"$1":', $content);

            return $json;
        }

        return false;
    }
}
