<?php

namespace App\Providers;

use App\Contracts\RssFeedContract;
use App\Services\GuardianService;
use Illuminate\Support\ServiceProvider;

class ContractServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(RssFeedContract::class, GuardianService::class);
    }
}
