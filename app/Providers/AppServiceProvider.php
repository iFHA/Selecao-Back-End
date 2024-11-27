<?php

namespace App\Providers;

use App\Models\Comment;
use App\Observers\CommentObserver;
use App\Repositories\Contracts\CommentRepository;
use App\Repositories\Contracts\Impl\Eloquent\CommentEloquentORMRepository;
use App\Repositories\Contracts\Impl\Eloquent\UserEloquentORMRepository;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepository::class, UserEloquentORMRepository::class);
        $this->app->bind(CommentRepository::class, CommentEloquentORMRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Comment::observe(CommentObserver::class);
    }
}
