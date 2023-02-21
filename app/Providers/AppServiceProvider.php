<?php

namespace App\Providers;

use App\Services\CalculateTotalByVolume;
use App\Services\CalculateTotalByWeight;
use App\Services\IPurchaseOrderService;
use App\Services\PurchaseOrderService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(IPurchaseOrderService::class, function (Application $app) {
            return new PurchaseOrderService(iterator_to_array($app->tagged('calculators')));
        });

        $this->app->tag([
            CalculateTotalByVolume::class,
            CalculateTotalByWeight::class,
        ], 'calculators');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
