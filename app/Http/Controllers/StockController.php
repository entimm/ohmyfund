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

    public function history(Request $request, $symbol)
    {
        $this->validate($request, [
            'type' => 'string',
            'begin' => 'date',
            'end' => 'date',
        ]);

        $begin = $request->get('begin');
        $end = $request->get('end');
        $type = $request->get('type');
        $histories = StockHistories::where('symbol', $symbol)
            ->where('type', $type)
            ->when($begin, function($query) use ($begin) {
                return $query->where('date', '>=', $begin);
            })->when($end, function($query) use ($end) {
                return $query->where('date', '<=', $end);
            })->get();
        return $histories;
    }
}
