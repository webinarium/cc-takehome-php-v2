<?php

namespace App\Providers;

use App\Services\IPurchaseOrderService;
use App\Services\PurchaseOrderService;
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
        $this->app->bind(IPurchaseOrderService::class, PurchaseOrderService::class);
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
