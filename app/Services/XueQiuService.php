<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;

class XueQiuService
{
    private $client;

    private $cookie;

    const BASE_PATH = 'https://xueqiu.com';
    const LOGIN_PATH = self::BASE_PATH;
    const POST_LOGIN_PATH = self::BASE_PATH.'/snowman/login';
    const STOCK_LIST_PATH = self::BASE_PATH.'/stock/forchartk/stocklist.json';

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->cookie = new FileCookieJar('cookie_jar.txt', true);
    }

    public function tryAuth()
    {
        if ($this->cookie->count()) {
            return true;
        }
        $response = $this->client->post(self::POST_LOGIN_PATH, [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; U; Linux x86; en-US; rv:1.9.0.5) Gecko',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            'cookies' => $this->cookie,
            'form_params' => [
                'remember_me' => true,
                'username' => env('XUEQIU_USERNAME'),
                'password' => env('XUEQIU_PASSWORD'),
            ],
            'verify' => false,
        ]);

        return $response->getStatusCode() == 200;
    }

    public function resolveQuotes($symbol)
    {
        $symbol = strtoupper($symbol);
        $response = $this->retryRequest(function () use ($symbol) {
            return $this->client->get('https://xueqiu.com/v4/stock/quote.json', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; U; Linux x86; en-US; rv:1.9.0.5) Gecko',
                ],
                'query' => [
                    'code' => $symbol,
                    '_' => microtime(),
                ],
                'cookies' => $this->cookie,
                'verify' => false,

            ]);
        }, 1);

        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);
        if (isset($data[$symbol])) {
            return $data[$symbol];
        }

        return false;
    }

    public function resolveHistory($symbol, $typeName, $countedAt = 0)
    {
        $symbol = strtoupper($symbol);
        $response = $this->retryRequest(function () use ($symbol, $typeName, $countedAt) {
            return $this->client->get('https://xueqiu.com/stock/forchartk/stocklist.json', [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (X11; U; Linux x86; en-US; rv:1.9.0.5) Gecko',
                ],
                'query' => [
                    'symbol' => $symbol,
                    'period' => '1day', // all、1day、1weel、1month
                    'type' => $typeName, // before 前复权、normal 不复权
                    'begin' => max(($countedAt - 86400) * 1000, 0),
                    'end' => microtime(),
                    '_' => microtime(),
                ],
                'cookies' => $this->cookie,
                'verify' => false,

            ]);
        }, 1);

        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);
        if (isset($data['chartlist'])) {
            return $data['chartlist'];
        }

        return false;
    }

    private function retryRequest(callable $callback, $sleep = 0)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            if ($sleep) {
                usleep($sleep * 1000);
            }
            $this->cookie->clear();
            return $callback();
        }
    }
}
