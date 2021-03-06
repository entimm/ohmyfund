<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Cache;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function stock(Stock $stock)
    {
        return view('stock', compact('stock'));
    }

    public function fund(Fund $fund)
    {
        return view('fund', compact('fund'));
    }

    public function rank(Request $request, Fund $fund)
    {
        $columns = [
            'rate'         => ['name' => '增长率', 'sortedBy' => 'asc'],
            'in_1week'     => ['name' => '近1周', 'sortedBy' => 'asc'],
            'in_1month'    => ['name' => '近1月', 'sortedBy' => 'asc'],
            'in_3month'    => ['name' => '近3月', 'sortedBy' => 'asc'],
            'in_6month'    => ['name' => '近6月', 'sortedBy' => 'asc'],
            'current_year' => ['name' => '今年', 'sortedBy' => 'asc'],
            'in_1year'     => ['name' => '近1年', 'sortedBy' => 'asc'],
            'in_2year'     => ['name' => '近2年', 'sortedBy' => 'asc'],
            'in_3year'     => ['name' => '近3年', 'sortedBy' => 'asc'],
            'in_5year'     => ['name' => '近5年', 'sortedBy' => 'asc'],
            'since_born'   => ['name' => '成立来', 'sortedBy' => 'asc'],
            'born_date'    => ['name' => '成立日期', 'sortedBy' => 'asc'],
        ];
        $orderBy = $request->input('orderBy', 'rate');
        $sortedBy = $request->input('sortedBy', 'desc');
        if ($orderBy && isset($columns[$orderBy]['sortedBy'])) {
            $columns[$orderBy]['sortedBy'] = $sortedBy == 'asc' ? 'desc' : 'asc';
        }
        $funds = $fund->toShows($orderBy, $sortedBy);

        return view('rank', compact('funds', 'columns'));
    }

    public function watchlist2(Request $request)
    {
        $graphScope = $request->input('graphScope', 100);
        $orderBy = $request->input('orderBy', 'evaluateRate');
        $sortedBy = $request->input('sortedBy', 'desc');
        $columns = [
            'rate'         => ['name' => '增长率', 'sortedBy' => 'asc'],
            'in_1week'     => ['name' => '近1周', 'sortedBy' => 'asc'],
            'in_1month'    => ['name' => '近1月', 'sortedBy' => 'asc'],
            'in_3month'    => ['name' => '近3月', 'sortedBy' => 'asc'],
            'in_6month'    => ['name' => '近6月', 'sortedBy' => 'asc'],
            'current_year' => ['name' => '今年', 'sortedBy' => 'asc'],
            'in_1year'     => ['name' => '近1年', 'sortedBy' => 'asc'],
            'in_2year'     => ['name' => '近2年', 'sortedBy' => 'asc'],
            'in_3year'     => ['name' => '近3年', 'sortedBy' => 'asc'],
            'in_5year'     => ['name' => '近5年', 'sortedBy' => 'asc'],
            'since_born'   => ['name' => '成立来', 'sortedBy' => 'asc'],
            'born_date'    => ['name' => '成立日期', 'sortedBy' => 'asc'],
        ];
        $funds = Collection::make();
        foreach (config('local.watchlist', []) as $codes) {
            $funds = $funds->merge(Fund::whereIn('code', $codes)->get());
        }
        $funds = $funds->sortBy($orderBy, SORT_REGULAR, $sortedBy == 'desc');
        $page = $request->input('page', '1');
        $funds = new LengthAwarePaginator($funds->forPage($page, 20), count($funds), 20, $page, [
            'path' => $request->getRequestUri(),
        ]);

        return view('watchlist2', compact('funds', 'columns', 'graphScope', 'orderBy', 'sortedBy'));
    }

    public function watchlist1(Request $request)
    {
        $orderBy = $request->input('orderBy', 'evaluateRate');
        $sortedBy = $request->input('sortedBy', 'desc');
        $columns = [
            'rate'         => ['name' => '增长率', 'sortedBy' => 'asc'],
            'in_1week'     => ['name' => '近1周', 'sortedBy' => 'asc'],
            'in_1month'    => ['name' => '近1月', 'sortedBy' => 'asc'],
        ];
        if ($orderBy && isset($columns[$orderBy])) {
            $columns[$orderBy]['sortedBy'] = $request->input('sortedBy') == 'asc' ? 'desc' : 'asc';
        }
        $funds = Collection::make();
        foreach (config('local.watchlist', []) as $codes) {
            $funds = $funds->merge(Fund::whereIn('code', $codes)->get());
        }
        $funds = $funds->sortBy($orderBy, SORT_REGULAR, $sortedBy == 'desc');
        $page = $request->input('page', '1');
        $funds = new LengthAwarePaginator($funds->forPage($page, 18), count($funds), 18, $page, [
            'path' => $request->getRequestUri(),
        ]);

        return view('watchlist1', compact('funds', 'columns', 'orderBy', 'sortedBy'));
    }

    public function evaluate(Request $request, Fund $fund)
    {
        $graphScope = $request->input('graphScope', 100);
        $orderBy = $request->input('orderBy', 'evaluateRate');
        $sortedBy = $request->input('sortedBy', 'desc');
        $columns = [
            'evaluateRate' => ['name' => '估算', 'sortedBy' => 'asc'],
            'evaluateTime' => ['name' => '时间'],
        ];
        if ($orderBy && isset($columns[$orderBy]['sortedBy'])) {
            $columns[$orderBy]['sortedBy'] = $request->input('sortedBy') == 'asc' ? 'desc' : 'asc';
        }
        $collection = Collection::make();
        foreach (config('local.watchlist', []) as $codes) {
            $funds = Fund::whereIn('code', $codes)->get()->sortBy($orderBy, SORT_REGULAR, $sortedBy == 'desc');
            $collection->push($funds);
        }

        return view('evaluate', compact('collection', 'columns', 'graphScope', 'orderBy', 'sortedBy'));
    }

    public function compare()
    {
        $stocks = explode(',', env('COMPARE_STOCKS'));
        $funds = explode(',', env('COMPARE_FUNDS'));
        $compareStocksJson = Stock::select(['symbol', 'name as title'])->whereIn('symbol', $stocks)->get()->toJson();
        $compareFundsJson = Fund::select(['code', 'name as title'])->whereIn('code', $funds)->get()->toJson();

        return view('compare', compact('compareStocksJson', 'compareFundsJson'));
    }
}
