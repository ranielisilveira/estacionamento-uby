<?php

namespace App\Providers;

use App\Domain\Contracts\Repositories\CustomerRepositoryInterface;
use App\Domain\Contracts\Repositories\OperatorRepositoryInterface;
use App\Domain\Contracts\Repositories\ParkingSpotRepositoryInterface;
use App\Domain\Contracts\Repositories\ReservationRepositoryInterface;
use App\Infrastructure\Repositories\EloquentCustomerRepository;
use App\Infrastructure\Repositories\EloquentOperatorRepository;
use App\Infrastructure\Repositories\EloquentParkingSpotRepository;
use App\Infrastructure\Repositories\EloquentReservationRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind repository interfaces to their Eloquent implementations
        $this->app->bind(
            OperatorRepositoryInterface::class,
            EloquentOperatorRepository::class
        );

        $this->app->bind(
            CustomerRepositoryInterface::class,
            EloquentCustomerRepository::class
        );

        $this->app->bind(
            ParkingSpotRepositoryInterface::class,
            EloquentParkingSpotRepository::class
        );

        $this->app->bind(
            ReservationRepositoryInterface::class,
            EloquentReservationRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
