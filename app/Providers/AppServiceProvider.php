<?php

namespace App\Providers;

use App\Models\Cfp;
use App\Models\CfpSubmission;
use App\Models\Community;
use App\Models\Event;
use App\Policies\CfpPolicy;
use App\Policies\CfpSubmissionPolicy;
use App\Policies\CommunityPolicy;
use App\Policies\EventPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        Gate::policy(Community::class, CommunityPolicy::class);
        Gate::policy(Event::class, EventPolicy::class);
        Gate::policy(Cfp::class, CfpPolicy::class);
        Gate::policy(CfpSubmission::class, CfpSubmissionPolicy::class);

        if (is_dir(resource_path('views/vendor/mail'))) {
            View::addNamespace('mail', resource_path('views/vendor/mail'));
        }
    }
}
