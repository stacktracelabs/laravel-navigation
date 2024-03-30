<?php


namespace StackTrace\Navigation;


use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database');

        $this->app->singleton('navigation', NavigationService::class);

        Relation::morphMap([
            'menu' => Menu::class,
            'link' => Link::class,
        ]);
    }

    public function boot(): void
    {
        Menu::observe(MenuObserver::class);
    }
}
