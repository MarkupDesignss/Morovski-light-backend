<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\SocialiteManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->bind(Factory::class, function ($app) {
        return new SocialiteManager($app);
    });
    }

    /**
     * Bootstrap any application services.
     */
      public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
