<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Article;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Define the 'manage-article' Gate
        Gate::define('manage-article', function (User $user, Article $article) {
            // A user can manage an article if they are an admin
            // OR if they are the author of the article.
            return $user->role === 'admin' || $user->id === $article->user_id;
        });
    }
}
