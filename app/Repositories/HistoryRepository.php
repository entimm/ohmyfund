<?php

namespace App\Repositories;

use DB;
use App\Entities\History;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class HistoryRepository.
 */
class HistoryRepository extends BaseRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return History::class;
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
     * @param $records
     * @param $fundCode
     *
     * @return int
     */
    public function saveRecords($records, $fundCode)
    {
        // 开启事务，保证下面sql语句一起执行成功
        $touchNum = 0;
        DB::transaction(function () use ($records, $fundCode, &$touchNum) {
            foreach ($records as $key => $record) {
                $record['code'] = $fundCode;
                $history = History::firstOrNew(array_only($record, ['code', 'date'], $record));
                // 如果存在数据，那么就停止后续数据库操作
                if ($history->exists) {
                    break;
                }
                $history->save();
                $touchNum++;
            }
        });

        return $touchNum;
    }
}
