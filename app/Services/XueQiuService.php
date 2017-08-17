<?php

namespace App\Services;

use App\Traits\HttpRequest;
use GuzzleHttp\Cookie\FileCookieJar;

class XueQiuService
{
    use HttpRequest;

    private $cookie;

    const BASE_PATH = 'https://xueqiu.com';
    const LOGIN_PATH = self::BASE_PATH;
    const POST_LOGIN_PATH = self::BASE_PATH.'/snowman/login';
    const STOCK_LIST_PATH = self::BASE_PATH.'/stock/forchartk/stocklist.json';

    /**
     * XueQiuService constructor.
     */
    public function __construct()
    {
        $this->cookie = new FileCookieJar('cookie_jar.txt', true);
        $this->tryAuth();
    }

    /**
     * 登陆认证雪球.
     */
    public function tryAuth()
    {
        if ($this->cookie->count()) {
            return;
        }
        $this->post(self::POST_LOGIN_PATH, [
            'remember_me' => true,
            'username'    => env('XUEQIU_USERNAME'),
            'password'    => env('XUEQIU_PASSWORD'),
        ], [
            'cookies' => $this->cookie,
            'verify'  => false,
        ]);
    }

    /**
     * 获取当前报价.
     *
     * @param $symbol
     *
     * @return array|bool
     */
    public function requestQuotes($symbol)
    {
        $symbol = strtoupper($symbol);
        $data = $this->retryRequest(function () use ($symbol) {
            return $this->get('https://xueqiu.com/v4/stock/quote.json', [
                'code' => $symbol,
                '_'    => microtime(),
            ], [
                'cookies' => $this->cookie,
                'verify'  => false,
            ]);
        }, 1);

        if (isset($data[$symbol])) {
            return $data[$symbol];
        }

        return false;
    }

    /**
     * 获取历史.
     *
     * @param     $symbol
     * @param     $typeName
     * @param int $countedAt
     *
     * @return array|bool
     */
    public function requestHistory($symbol, $typeName, $countedAt = 0)
    {
        $symbol = strtoupper($symbol);
        $data = $this->retryRequest(function () use ($symbol, $typeName, $countedAt) {
            return $this->get('https://xueqiu.com/stock/forchartk/stocklist.json', [
                'symbol' => $symbol,
                'period' => '1day', // all、1day、1weel、1month
                'type'   => $typeName, // before 前复权 \ normal 不复权
                'begin'  => max(($countedAt - 86400) * 1000, 0),
                'end'    => microtime(),
                '_'      => microtime(),
            ], [
                'cookies' => $this->cookie,
                'verify'  => false,
            ]);
        }, 1);

        if (isset($data['chartlist'])) {
            return $data['chartlist'];
        }

        return false;
    }

    /**
     * 获取内容, 如果失败一次则清除 cookie 后尝试认证, 然后再进行内容的获取.
     *
     * @param callable $callback
     * @param int      $sleep
     *
     * @return mixed
     */
    private function retryRequest(callable $callback, $sleep = 0)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            if ($sleep) {
                usleep($sleep * 1000);
            }
            $this->cookie->clear();
            $this->tryAuth();

            return $callback();
        }
    }
}
