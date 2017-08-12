<?php

namespace App\Repositories;

use DB;
use App\Entities\StockHistories;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class StockHistoryRepository.
 */
class StockHistoryRepository extends BaseRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return StockHistories::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * 保存基金历史记录，并返回操作数量.
     *
     * @param $list
     * @param $symbol
     * @param $type
     *
     * @return int
     */
    public function saveRecords($list, $symbol, $type)
    {
        // 开启事务，保证下面sql语句一起执行成功
        $touchNum = 0;
        DB::transaction(function () use ($list, $symbol, $type, &$touchNum) {
            foreach ($list as $item) {
                $date = date('Y-m-d', $item['timestamp'] / 1000);
                $uniqueKeys = [
                    'symbol' => $symbol,
                    'date' => $date,
                    'type' => $type,
                ];
                $history = StockHistories::firstOrCreate($uniqueKeys, $item);
                if (! $history->wasRecentlyCreated) {
                    break;
                }
                $touchNum++;
            }
        });

        return $touchNum;
    }

    /**
     * 蜡烛图数据.
     *
     * @param $symbol
     * @param $type
     * @param $begin
     * @param $end
     */
    public function candlestick($symbol, $type, $begin, $end)
    {
        return $this->scopeQuery(function ($query) use ($symbol, $type, $begin, $end) {
            return $query->select([
                    'open',
                    'high',
                    'low',
                    'close',
                    'volume',
                    'date',
                ])->where('symbol', $symbol)
                ->where('type', $type)
                ->when($begin, function ($query) use ($begin) {
                    return $query->where('date', '>=', $begin);
                })->when($end, function ($query) use ($end) {
                    return $query->where('date', '<=', $end);
                })->orderBy('date', 'asc');
        })->all();
    }

    /**
     * 收盘数据.
     *
     * @param $symbol
     * @param $type
     * @param $begin
     * @param $end
     *
     * @return mixed
     */
    public function values($symbol, $type, $begin, $end)
    {
        return $this->scopeQuery(function ($query) use ($symbol, $type, $begin, $end) {
            return $query->select(['close', 'date'])
                ->where('symbol', $symbol)
                ->where('type', $type)
                ->when($begin, function ($query) use ($begin) {
                    return $query->where('date', '>=', $begin);
                })->when($end, function ($query) use ($end) {
                    return $query->where('date', '<=', $end);
                });
        })->orderBy('date', 'asc')
          ->all();
    }
}
