<?php

namespace App\Providers;

use App\Models\SystemSetting;
use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability, array $arguments): ?bool {
            if (! $user instanceof User || ! $user->hasRole('Super Admin')) {
                return null;
            }

            $model = $arguments[0] ?? null;

            if ($ability === 'delete' && $model instanceof User && $model->hasRole('Super Admin')) {
                return false;
            }

            if ($ability === 'toggleStatus' && $model instanceof User && $model->is($user)) {
                return false;
            }

            return true;
        });
        Gate::policy(Role::class, RolePolicy::class);

        try {
            if (! Schema::hasTable('system_settings')) {
                return;
            }

            $timezone = SystemSetting::getSettings()->time_zone;
        } catch (\Throwable) {
            return;
        }

        if (in_array($timezone, timezone_identifiers_list(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }
    }
}
