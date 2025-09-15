<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Event;
use App\Models\User;
use App\Policies\V1\EventPolicy;
use App\Policies\V1\UserPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
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
        Model::unguard();

        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
