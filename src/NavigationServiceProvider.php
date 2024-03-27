<?php


namespace StackTrace\Navigation;


use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database');

        Relation::morphMap([
            'menu' => Menu::class,
            'link' => Link::class,
        ]);
    }
}
