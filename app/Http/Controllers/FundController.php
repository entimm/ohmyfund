<?php

namespace App\Http\Controllers;

use App\Entities\Fund;
use App\Entities\History;
use Illuminate\Http\Request;
use App\Repositories\HistoryRepository;

class FundController extends Controller
{
    /**
     * @var HistoryRepository
     */
    private $historyRepository;

    /**
     * FundController constructor.
     *
     * @param HistoryRepository $historyRepository
     */
    public function __construct(HistoryRepository $historyRepository)
    {
        $this->historyRepository = $historyRepository;
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
            'end' => 'date',
        ]);

        $begin = $request->get('begin');
        $end = $request->get('end');

        return $this->historyRepository->history($code, $begin, $end)['data'];
    }
}
