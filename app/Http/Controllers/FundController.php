<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\History;
use App\Models\History;
use Illuminate\Http\Request;

class FundController extends Controller
{
    /**
     * @var History
     */
    private $history;

    /**
     * FundController constructor.
     *
     * @param History $history
     */
    public function __construct(History $history)
    {
        $this->history = $history;
    }

    public function index(Request $request)
    {
        return Fund::get();
    }

    /**
     * 基金的当前数据.
     *
     * @param Fund $fund
     *
     * @return Fund
     */
    public function show(Fund $fund)
    {
        return $fund;
    }

    /**
     * 基金的历史净值
     *
     * @param Request $request
     * @param         $code
     *
     * @return mixed
     */
    public function history(Request $request, $code)
    {
        $this->validate($request, [
            'begin' => 'date',
            'end'   => 'date',
        ]);

        $begin = $request->get('begin');
        $end = $request->get('end');

        return $this->history->history($code, $begin, $end)['data'];
    }

    public function event(Request $request, $code)
    {
        $this->validate($request, [
            'begin' => 'date',
            'end'   => 'date',
        ]);

        $begin = $request->get('begin');
        $end = $request->get('end');

        return $this->history->event($code, $begin, $end);
    }
}
