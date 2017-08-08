<?php

namespace App\Http\Controllers;

use App\Entities\Fund;
use App\Entities\History;
use Illuminate\Http\Request;
use App\Repositories\FundRepository;

class FundController extends Controller
{
    /**
     * @var FundRepository
     */
    private $fundRepository;

    /**
     * FundController constructor.
     *
     * @param FundRepository $fundRepository
     */
    public function __construct(FundRepository $fundRepository)
    {
        $this->fundRepository = $fundRepository;
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
