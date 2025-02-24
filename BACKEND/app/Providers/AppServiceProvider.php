<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Observers\SupportTicketObserver;
use App\Models\Supportticket;
use App\Observers\SupportTicketNotificationObserver;
use App\Models\Supportticketnotification;
use App\Models\Tenant;
use App\Observers\TenantObserver;

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
        Supportticket::observe(SupportTicketObserver::class);
        SupportticketNotification::observe(SupportTicketNotificationObserver::class);
        Tenant::observe(TenantObserver::class);
    }
}
