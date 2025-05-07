<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\SupportTicketObserver;
use App\Models\SupportTicket;
use App\Observers\SupportTicketNotificationObserver;
use App\Models\SupportTicketNotification;
use App\Models\Tenant;
use App\Observers\TenantObserver;
use App\Observers\LiveAgentObserver;
use App\Models\LiveAgents;
use Illuminate\Support\Facades\Route;
use App\Models\Detail;
use App\Models\Priority;
use App\Observers\PriorityObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::bind('uinId', function ($value) {
            return Detail::where('uin', $value)->firstOrFail();
        });
        Supportticket::observe(SupportTicketObserver::class);
    //    SupportTicketNotification::observe(SupportTicketNotificationObserver::class);
        Tenant::observe(TenantObserver::class);
        LiveAgents::observe(LiveAgentObserver::class);
        Priority::observe(PriorityObserver::class);
    }
}
