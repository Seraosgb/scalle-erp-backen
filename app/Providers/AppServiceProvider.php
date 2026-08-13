<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Fiscal\Contracts\FiscalDriverInterface;
use App\Modules\Fiscal\Drivers\MockFiscalDriver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Associa a Interface à implementação concreta
        $this->app->bind(FiscalDriverInterface::class, MockFiscalDriver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}