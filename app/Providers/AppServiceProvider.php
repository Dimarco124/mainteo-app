<?php

namespace App\Providers;

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
        // Partager le compteur de notifications non lues dans toutes les vues
        view()->composer('*', function ($view) {
            if (auth()->check() && in_array(auth()->user()->type_utilisateur, ['admin', 'superviseur_client', 'superviseur_soutarah', 'demandeur', 'technicien', 'chef technicien'])) {
                $notificationsNonLues = \App\Models\InterventionNotification::where('user_id', auth()->id())
                    ->where('statut', 'non_lu')
                    ->count();
                $view->with('notificationsNonLues', $notificationsNonLues);
            } else {
                $view->with('notificationsNonLues', 0);
            }
        });
    }
}
