<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Entities\Fund;
use Illuminate\Console\Command;
use App\Services\EastmoneyService;
use Illuminate\Support\Facades\Log;
use App\Exceptions\NonDataException;
use App\Repositories\FundRepository;
use App\Exceptions\ValidateException;
use App\Repositories\HistoryRepository;
use App\Exceptions\ResolveErrorException;

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
     * @var HistoryRepository
     */
    protected $historyRepository;

    /**
     * @var FundRepository
     */
    private $fundRepository;

    /**
     * Create a new command instance.
     *
     * @param HistoryRepository $historyRepository
     * @param FundRepository    $fundRepository
     */
    public function __construct(HistoryRepository $historyRepository, FundRepository $fundRepository)
    {
        parent::__construct();

        $this->historyRepository = $historyRepository;
        $this->fundRepository = $fundRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('update history 🙏');
        $funds = $this->fundRepository->toUpdates();
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

    /**
     * 更新单个基金的历史净值
     *
     * @param Fund $fund
     *
     * @return int
     */
    protected function updateOneFund(Fund $fund)
    {
        try {
            // 通过 profit_date 判断这只基金是否有被处理过
            $records = resolve(EastmoneyService::class)->requestHistories($fund->code, $fund->counted_at);
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
            Log::error($e->getMessage(), [
                'fund_code' => $fund->code,
            ]);
            $fund->status = 5;

            return 0;
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'fund_code' => $fund->code,
                'where' => $e->getFile().':'.$e->getLine(),
            ]);

            return 0;
        }

        $touchNum = $this->historyRepository->saveRecords($records, $fund->code);
        // 标记10天内都没数据的基金
        $diffDay = date_diff(date_create($fund->profit_date), date_create())->days;
        if ($diffDay > 10) {
            $fund->status = 4;
        }
        $fund->profit_date = $records[0]['date'] ?: null;
        $fund->counted_at = Carbon::now();

        return $touchNum;
    }
}
