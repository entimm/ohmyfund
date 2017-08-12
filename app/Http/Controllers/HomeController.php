<?php

namespace App\Http\Controllers;

use App\Entities\Fund;
use App\Entities\History;
use App\Entities\Stock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\FundRepository;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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

    public function rank(Request $request, FundRepository $fundRepository)
    {
        $columns = [
            'rate' => ['name' => '增长率', 'sortedBy' => 'asc'],
            'in_1week' => ['name' => '近1周', 'sortedBy' => 'asc'],
            'in_1month' => ['name' => '近1月', 'sortedBy' => 'asc'],
            'in_3month' => ['name' => '近3月', 'sortedBy' => 'asc'],
            'in_6month' => ['name' => '近6月', 'sortedBy' => 'asc'],
            'current_year' => ['name' => '今年', 'sortedBy' => 'asc'],
            'in_1year' => ['name' => '近1年', 'sortedBy' => 'asc'],
            'in_2year' => ['name' => '近2年', 'sortedBy' => 'asc'],
            'in_3year' => ['name' => '近3年', 'sortedBy' => 'asc'],
            'in_5year' => ['name' => '近5年', 'sortedBy' => 'asc'],
            'since_born' => ['name' => '成立来', 'sortedBy' => 'asc'],
            'born_date' => ['name' => '成立日期', 'sortedBy' => 'asc'],
        ];
        $orderBy = $request->input('orderBy');
        if ($orderBy && isset($columns[$orderBy])) {
            $columns[$orderBy]['sortedBy'] = $request->input('sortedBy') == 'asc' ? 'desc' : 'asc';
        }
        $funds = $fundRepository->toShows()['data'];
        return view('rank', compact('funds', 'columns'));
    }


    public function concerns()
    {
        foreach (config('local.concerns', []) as $codes) {
            $funds = Fund::whereIn('code', $codes)->get();
        }
        return view('concerns', compact('funds'));
    }
}
