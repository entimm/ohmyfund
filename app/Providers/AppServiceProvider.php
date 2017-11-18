<?php

namespace App\Providers;

use App\Models\Stock;
use Illuminate\Support\ServiceProvider;
use View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Resources\Json\Resource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        try {
            $stocks = Stock::select(['symbol', 'name'])->get();
            View::share('stocks', $stocks);
        } catch (\Exception $e) {
        }

        Resource::withoutWrapping();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
