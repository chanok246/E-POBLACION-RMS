<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Pagination\Paginator;
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
        // TAGLISH:
        // Ginagamit natin ang Bootstrap 5 pagination.
        //
        // Dahil Bootstrap 5 ang UI ng e-Poblacion,
        // kailangan ding Bootstrap ang design ng pagination.
        //
        // Ito ang mag-aayos sa:
        // - Previous / Next buttons
        // - Page numbers
        // - Pagination arrows
        // - Spacing at alignment
        Paginator::useBootstrapFive();


        /*
        |--------------------------------------------------------------------------
        | NOTIFICATION BELL
        |--------------------------------------------------------------------------
        |
        | TAGLISH:
        | Ipinapasa natin ang notification data sa main application layout.
        |
        | Dahil ang layouts.app ay ginagamit ng iba't ibang pages,
        | magiging available ang $notifications sa bawat page na gumagamit
        | ng main layout.
        |
        | Hindi tayo gumagawa ng bagong database table dito.
        | Ang NotificationService ang kumukuha ng reminders mula sa:
        |
        | - Relief Assistance Operations
        | - Person Complaint hearings
        |
        */

        View::composer(
            'layouts.app',
            function ($view) {

                $notifications = app(
                    NotificationService::class
                )->getNotifications();

                $view->with(
                    'notifications',
                    $notifications
                );
            }
        );
    }
}