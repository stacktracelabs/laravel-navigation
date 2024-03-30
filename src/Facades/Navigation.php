<?php


namespace StackTrace\Navigation\Facades;


use Illuminate\Support\Facades\Facade;

/**
 * @method static \StackTrace\Navigation\Menu|null findNavigationByHandle(string $handle)
 * @method static \Illuminate\Support\Collection findNavigationsByHandle(array $handles)
 * @method static void loadLinkResourceWith(array $morphMap)
 * @method static void enableCache(bool $enable = true)
 */
class Navigation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'navigation';
    }
}
