<?php


namespace StackTrace\Navigation;


use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database');
    }
}
