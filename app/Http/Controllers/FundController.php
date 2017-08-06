<?php

namespace App\Http\Controllers;

use App\Fund;
use App\History;
use Illuminate\Http\Request;

class FundController extends Controller
{
    public function index(Request $request)
    {
        return Fund::get();
    }

    public function show(Fund $fund)
    {
        return $fund;
    }

    public function history(Request $request, $code)
    {
        $this->validate($request, [
            'begin' => 'date',
            'end' => 'date',
        ]);

        $begin = $request->get('begin');
        $end = $request->get('end');
        $histories = History::select(['date', 'unit', 'rate'])
            ->where('code', $code)
            ->when($begin, function ($query) use ($begin) {
                return $query->where('date', '>=', $begin);
            })->when($end, function ($query) use ($end) {
                return $query->where('date', '<=', $end);
            })->get();

        return $histories;
    }
}
