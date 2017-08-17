<?php

use App\Services\EastmoneyService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('update', function () {
    $this->call('update:companies');
    $this->call('update:funds');
    $this->call('update:ranks');
    $this->call('update:histories');
})->describe('Update all');

Artisan::command('evaluate {--f|force}', function ($force) {
    $headers = [
        '代码',
        '名称',
        '估算增长率',
        '估值时间',
    ];
    foreach (config('local.concerns', []) as $codes) {
        $list = Collection::make($codes)->map(function (&$item, $key) use ($force) {
            return resolve(EastmoneyService::class)->resolveEvaluateAndCache($item, $force);
        })->sortBy('rate', SORT_REGULAR, 'desc')
          ->transform(function ($item) {
              return [
                $item['code'],
                $item['name'],
                $item['rate'],
                $item['time'],
            ];
          });
        $this->table($headers, $list);
    }
});
