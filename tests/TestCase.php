<?php


namespace StackTrace\Navigation\Tests;


use Orchestra\Testbench\TestCase as Orchestra;
use StackTrace\Navigation\NavigationServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            NavigationServiceProvider::class,
        ];
    }
}
