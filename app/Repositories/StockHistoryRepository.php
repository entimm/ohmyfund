<?php

namespace App\Repositories;

use App\Entities\StockHistories;
use DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Entities\History;

/**
 * Class StockHistoryRepository
 * @package namespace App\Repositories\Eloquent;
 */
class StockHistoryRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return StockHistories::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * 保存基金历史记录，并返回操作数量
     *
     * @param $records
     * @param $fundCode
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
}
