<?php

namespace App\Http\Controllers;

use App\Entities\Stock;
use App\Entities\StockHistories;
use Illuminate\Http\Request;

class StockController extends Controller
{

    /**
     * @param Request $request
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index(Request $request)
    {
        return Stock::get();
    }


    /**
     * @param Stock $stock
     *
     * @return Stock
     */
    public function show(Stock $stock)
    {
        return $stock;
    }


    /**
     * 股票蜡烛图历史数据
     *
     * @param Request $request
     * @param         $symbol
     *
     * @return mixed
     */
    public function candlesticks(Request $request, $symbol)
    {
        $this->validate($request, [
            'type' => 'string',
            'begin' => 'date',
            'end' => 'date',
        ]);

        $begin = $request->get('begin');
        $end = $request->get('end');
        $type = $request->get('type') ?: 1;
        $candlestick = StockHistories::where('symbol', $symbol)
            ->select([
                'open',
                'high',
                'low',
                'close',
                'volume',
                'date',
            ])
            ->where('type', $type)
            ->when($begin, function ($query) use ($begin) {
                return $query->where('date', '>=', $begin);
            })->when($end, function ($query) use ($end) {
                return $query->where('date', '<=', $end);
            })->get();

        return $candlestick;
    }


    /**
     * 股票收市值历史
     *
     * @param Request $request
     * @param         $symbol
     *
     * @return mixed
     */
    public function values(Request $request, $symbol)
    {
        $this->validate($request, [
            'type' => 'string',
            'begin' => 'date',
            'end' => 'date',
        ]);

        $begin = $request->get('begin');
        $end = $request->get('end');
        $type = $request->get('type') ?: 1;
        $values = StockHistories::where('symbol', $symbol)
            ->select(['close', 'date'])
            ->where('type', $type)
            ->when($begin, function ($query) use ($begin) {
                return $query->where('date', '>=', $begin);
            })->when($end, function ($query) use ($end) {
                return $query->where('date', '<=', $end);
            })->get();

        return $values;
    }
}
