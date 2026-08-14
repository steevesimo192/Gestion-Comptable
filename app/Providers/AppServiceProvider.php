<?php

namespace App\Providers;

use App\Repositories\Contracts\DeviseRepositoryInterface;
use App\Repositories\DeviseRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(DeviseRepositoryInterface::class, DeviseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Schema::defaultStringLength(length:191);
    }
}
