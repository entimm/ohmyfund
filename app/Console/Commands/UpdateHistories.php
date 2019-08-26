<?php

namespace App\Console\Commands;

use App\Exceptions\NonDataException;
use App\Exceptions\ResolveErrorException;
use App\Exceptions\ValidateException;
use App\Models\Fund;
use App\Models\History;
use App\Services\EastmoneyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
     * @var History
     */
    protected $history;

    /**
     * @var Fund
     */
    private $fund;

    /**
     * Create a new command instance.
     *
     * @param History $history
     * @param Fund    $fund
     */
    public function __construct(History $history, Fund $fund)
    {
        parent::__construct();

        $this->history = $history;
        $this->fund = $fund;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('update history 🙏');
        $funds = $this->fund->toUpdates();
        $count = count($funds);
        foreach ($funds as $key => $fund) {
            $touchNum = $this->updateOneFund($fund);
            // 进度百分数
            $processPercent = str_pad(round(($key + 1) * 100 / $count, 2).'%', 7, ' ', STR_PAD_LEFT);
            // 进度 | 最新收益日期 | 基金代码 | 更新条数
            $date = $fund->profit_date ?: '0000-00-00';
            $touchNum && $this->info("😃{$processPercent} | {$date} | {$fund->code} | {$touchNum}");
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
            $sdate = $fund->counted_at ?? $fund->born_date;
            if (!$sdate) {
                return 0;
            }
            if ($sdate instanceof Carbon) {
                $sdate = $sdate->toDateString();
            }

            $edate = Carbon::createFromFormat('Y-m-d', $sdate)->addDays(65);
            if ($edate->gt($now = Carbon::today()->subDay())) {
                $edate = $now;
            }

            if (Carbon::createFromFormat('Y-m-d', $sdate)->gte($edate)) {
                // $this->warn('满了哦');
                return 0;
            }
            $edate = $edate->toDateString();

            $fund->status = 0;
            $records = resolve(EastmoneyService::class)->requestHistories($fund->code, $sdate, $edate);
        } catch (NonDataException $e) {
            // 如果没有历史就进行标记
            $fund->status = 3;

            return 0;
        } catch (ResolveErrorException $e) {
            Log::error($e->getMessage(), [
                'fund_code' => $fund->code,
                'row'       => $e->getData(),
            ]);
            $fund->status = 5;
            $this->error("ResolveErrorException happen, fund code is {$fund->code}, err is {$e->getMessage()}");

            return 0;
        } catch (ValidateException $e) {
            Log::error($e->getMessage(), [
                'fund_code' => $fund->code,
            ]);
            $fund->status = 5;
            $this->error("ValidateException happen, fund code is {$fund->code},".$e->getMessage());

            return 0;
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [
                'fund_code' => $fund->code,
                'where'     => $e->getFile().':'.$e->getLine(),
            ]);
            $fund->status = 5;
            $this->error("{$e->getMessage()}, fund code is {$fund->code}");

            return 0;
        }

        $touchNum = $this->history->saveRecords($records, $fund->code);
        // 标记10天内都没数据的基金
        $fund->profit_date = $records[0]['date'] ?: $fund->profit_date;

        $fund->counted_at = $edate;

        return $touchNum;
    }
}
