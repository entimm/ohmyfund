<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Entities\Fund;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class FundRepository.
 */
class FundRepository extends BaseRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Fund::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * 获取即将更新的基金集合.
     */
    public function toUpdates()
    {
        return Fund::where(function ($query) {
            // 过滤掉今天结算过的
            $query->where('profit_date', '<', date('Y-m-d'))
                ->orWhereNull('profit_date');
        })->where(function ($query) {
            // 60分钟内更新过的不在更新
            $query->where('counted_at', '<', Carbon::now()->subMinutes(60))
                ->orWhereNull('counted_at');
        })->whereNotIn('status', [3, 4, 5]) // 过滤没有数据和极少数据、有异常的基金
        ->whereNotIn('type', [5, 8]) // 过滤货币基金、理财型基金
        ->get();
    }
}
