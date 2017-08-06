<?php

namespace App\Console\Commands;

use App\Exceptions\NonDataException;
use App\Exceptions\ResolveErrorException;
use App\Exceptions\ValidateException;
use App\Fund;
use App\Services\EastmoneyService;
use App\History;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UpdateHistories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:histories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update history';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('update history 🙏');
        $funds = Fund::where(function ($query) {
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
        $count = count($funds);
        foreach ($funds as $key => $fund) {
            $touchNum = $this->updateOneFund($fund);
            // 进度百分数
            $processPercent = str_pad(round(($key + 1) * 100 / $count, 2).'%', 7, ' ', STR_PAD_LEFT);
            // 进度 | 最新收益日期 | 基金代码 | 更新条数
            $this->info("😃{$processPercent} | {$fund->profit_date} | {$fund->code} | {$touchNum}");
            $fund->save();
        }
        $this->info('update history done 😎');
    }

    protected function updateOneFund($fund)
    {
        try {
            // 通过 profit_date 判断这只基金是否有被处理过
            $records = resolve(EastmoneyService::class)->history($fund->code, ! ! $fund->profit_date);
        } catch (NonDataException $e) {
            // 如果没有历史就进行标记
            $fund->status = 3;

            return 0;
        } catch (ResolveErrorException $e) {
            Log::error($e->getMessage(), [
                'fund_code' => $fund->code,
                'row' => $e->getData(),
            ]);
            $fund->status = 5;

            return 0;
        } catch (ValidateException $e) {
            Log::error('未通过数据验证', [
                'fund_code' => $fund->code,
                'msg' => $e->getMessage(),
            ]);
            $fund->status = 5;

            return 0;
        }

        $fund->profit_date = $records[0][0] ?: null;

        // 开启事务，保证下面sql语句一起执行成功
        $touchNum = 0;
        DB::transaction(function () use ($records, $fund, &$touchNum) {
            foreach ($records as $key => $record) {
                $history = History::firstOrNew([
                    'code' => $fund->code,
                    'date' => $record[0],
                ], [
                    'unit' => $record[1],
                    'total' => $record[2],
                    'rate' => $record[3],
                    'buy_status' => $record[4],
                    'sell_status' => $record[5],
                    'bonus' => $record[6],
                ]);
                // 如果存在数据，那么就停止后续数据库操作
                if ($history->exists) {
                    break;
                }
                $history->save();
                $touchNum++;
            }
        });
        // 标记10天内都没数据的基金
        $diffDay = date_diff(date_create($fund->profit_date), date_create())->days;
        if ($diffDay > 10) {
            $fund->status = 4;
        }
        $fund->counted_at = Carbon::now();

        return $touchNum;
    }
}
