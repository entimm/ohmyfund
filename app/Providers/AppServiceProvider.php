<?php

namespace App\Providers;

use App\Services\XueQiuService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('xueqiu', function ($app) {
            $xueQiu = resolve(XueQiuService::class);
            $xueQiu->tryAuth(env('XUEQIU_USERNAME'), env('XUEQIU_PASSWORD'));
            return $xueQiu;
        });
    }
}
