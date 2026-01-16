<?php

namespace App\Providers;

use App\Models\Book;
use App\Policies\BookPolicy;
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
        // Inventory / Books
        Book::class => BookPolicy::class,
        // Add more mappings here as you create policies:
        \App\Models\Book::class => \App\Policies\BookPolicy::class,
        \App\Models\Review::class => \App\Policies\ReviewPolicy::class,
        // \App\Models\Category::class => \App\Policies\CategoryPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /*
         * Optional: global overrides.
         * Return true to allow, false to deny, or null to defer to policies/gates.
         * Keep this conservative so it doesn't unintentionally bypass policies.
         */
        Gate::before(function ($user, string $ability) {
            // Example: if you want managers to be able to access a custom ability:
            // if ($user->role === 'manager' && $ability === 'manage-users') {
            //     return true;
            // }
            return null;
        });
    }
}
