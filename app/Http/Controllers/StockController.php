<?php

namespace App\Http\Controllers;

use App\Stock;
use App\StockHistories;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        return Stock::get();
    }

    public function show(Stock $stock)
    {
        return $stock;
    }

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
            ])
            ->where('type', $type)
            ->when($begin, function($query) use ($begin) {
                return $query->where('date', '>=', $begin);
            })->when($end, function($query) use ($end) {
                return $query->where('date', '<=', $end);
            })->get();
        return $candlestick;
    }

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
            ->when($begin, function($query) use ($begin) {
                return $query->where('date', '>=', $begin);
            })->when($end, function($query) use ($end) {
                return $query->where('date', '<=', $end);
            })->get();
        return $values;
    }
}
