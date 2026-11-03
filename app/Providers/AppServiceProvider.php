<?php

namespace App\Providers;

use App\Repositories\KaryawanRepository;
use App\Repositories\KaryawanRepositoryInterface;
use App\Repositories\PenggajianRepository;
use App\Repositories\PenggajianRepositoryInterface;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            KaryawanRepositoryInterface::class,
            KaryawanRepository::class,
        );

        $this->app->bind(
            PenggajianRepositoryInterface::class,
            PenggajianRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
