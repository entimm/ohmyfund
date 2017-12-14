<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Http\Resources\Fund as FundResource;
use App\Models\History;
use App\Http\Resources\History as HistoryResource;
use Illuminate\Http\Request;
use App\Services\EastmoneyService;

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
        return FundResource::collection(Fund::paginate());
    }

    /**
     * 基金的当前数据.
     *
     * @param Fund $fund
     *
     * @return FundResource|Fund
     */
    public function show(Fund $fund)
    {
        return new FundResource($fund);
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
        $limit = $request->get('limit');

        return HistoryResource::collection($this->history->history($code, $begin, $end, $limit));
    }

    public function event(Request $request, $code)
    {
        $this->validate($request, [
            'begin' => 'date',
            'end'   => 'date',
        ]);

        $begin = $request->get('begin');
        $end = $request->get('end');
        $limit = $request->get('graphScope');

        return $this->history->event($code, $begin, $end, $limit);
    }

    public function evaluate($noCache = null)
    {
        $collection = collect();
        $codes = collect(config('local.concerns'))->flatten();
        foreach ($codes as $code) {
            $result = resolve(EastmoneyService::class)->resolveEvaluateAndCache($code, $noCache);
            $result = array_only($result, ['code', 'rate', 'time']);
            $collection->push($result);
        }
        return $collection;
    }
}
