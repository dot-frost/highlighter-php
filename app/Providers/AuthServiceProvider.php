<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user) {
            return $user->hasRole('Super-Admin') ? true : null;
        });
        Gate::before(function (User $user, $ability, $arguments) {
            [$model, $action, $id] = collect(explode('.', $ability))->merge([null,null,null]);
            $id = $arguments[0]->id ?? $id;
            return $user->getAllPermissions()->first(function ($permission) use ($model, $action, $id, $user) {
                $permission = $permission->name;
                return in_array($permission, [
                    "*",
                    "{$model}",
                    "{$model}.*",
                    "*.{$action}",
                    "*.{$action}.*",
                    "*.{$action}.{$id}",
                    "{$model}.{$action}",
                    "{$model}.{$action}.*",
                    "{$model}.{$action}.{$id}",
                ]);
            },null);
        });
    }
}
