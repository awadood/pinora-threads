<?php

namespace App\Providers;

use App\Repository\BaseRepository;
use App\Repository\Contracts\IBaseRepository;
use App\Support\Roles;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [

        IBaseRepository::class => BaseRepository::class,

        // Catalog

        // Content

        // Core

        // Customer

        // Inventory

        // Order

        // Payment

        // Promotion

        // Shipping

        // Tax
    ];

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
        Gate::before(function ($user, $ability) {
            return $user->hasRole(Roles::SUPER_ADMIN) ? true : null;
        });
    }
}
