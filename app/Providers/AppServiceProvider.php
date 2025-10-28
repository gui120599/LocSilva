<?php

namespace App\Providers;

use App\Models\Aluguel;
use App\Observers\AluguelObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Aluguel::observe(AluguelObserver::class);
    }
}
