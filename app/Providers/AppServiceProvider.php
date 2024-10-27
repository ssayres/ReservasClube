<?php

namespace App\Providers;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Activity;
use App\Policies\ActivityPolicy;

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
        // Define a Gate para permitir a criação de atividades somente para professores
        Gate::define('create-activity', function (User $user) {
            return $user->plan_id == 4; // Permissão concedida somente para `plan_id = 4`
        });
    }

    protected $policies = [
        Activity::class => ActivityPolicy::class,
    ];
}
