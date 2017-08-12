<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use App\Repositories\StockHistoryRepository;

class StockController extends Controller
{
    /**
     * @var StockHistoryRepository
     */
    private $stockHistoryRepository;

    /**
     * StockController constructor.
     *
     * @param StockHistoryRepository $stockHistoryRepository
     */
    public function __construct(StockHistoryRepository $stockHistoryRepository)
    {
        $this->stockHistoryRepository = $stockHistoryRepository;
    }

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
     * 股票蜡烛图历史数据.
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
        $candlestick = $this->stockHistoryRepository->candlestick($symbol, $type, $begin, $end);

        return $candlestick;
    }

    /**
     * 股票收市值历史.
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
        $values = $this->stockHistoryRepository->values($symbol, $type, $begin, $end);

        return $values;
    }
}
