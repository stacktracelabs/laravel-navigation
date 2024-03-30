<?php


namespace StackTrace\Navigation;


use Closure;
use Fureev\Trees\DescendantsRelation;
use Fureev\Trees\QueryBuilder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class NavigationService
{
    /**
     * List of relations which should be eagerly loaded on the resource for each link.
     */
    protected static array $morphLinkResourcesWith = [];

    /**
     * Determine if navigation caching is enabled.
     */
    protected bool $enableCache = false;

    /**
     * Determine if navigation caching is enabled.
     */
    protected function shouldCache(): bool
    {
        return $this->enableCache;
    }

    /**
     * Enable caching of menu.
     */
    public function enableCache(bool $enable = true): void
    {
        $this->enableCache = $enable;
    }

    /**
     * Retrieve full navigation by handle.
     */
    public function findNavigationByHandle(string $handle): ?Menu
    {
        if ($this->shouldCache()) {
            if ($menu = $this->getCachedNavigation($handle)) {
                return $menu;
            }
        }

        $menu = $this->newMenuQuery()->firstWhere('handle', $handle);

        if (! $menu) {
            return null;
        }

        if ($this->shouldCache()) {
            $this->addNavigationToCache($menu);
        }

        return $menu;
    }

    /**
     * Determine if the navigation is cached.
     */
    protected function isCached(string $handle): bool
    {
        return Cache::has("Navigation:{$handle}");
    }

    /**
     * Add navigation to cache.
     */
    protected function addNavigationToCache(Menu $menu): void
    {
        $key = 'Navigation:'.$menu->handle;

        Cache::forever($key, $menu);
    }

    /**
     * Retrieve cached navigation.
     */
    protected function getCachedNavigation(string $handle): ?Menu
    {
        return Cache::get("Navigation:{$handle}");
    }

    /**
     * Invalidate cached navigation by handle.
     */
    public function invalidateCachedNavigation(string $handle): void
    {
        Cache::forget("Navigation:{$handle}");
    }

    /**
     * Run given callback without caching enabled.
     */
    public function withoutCache(Closure $callback): mixed
    {
        $enabled = $this->enableCache;

        $this->enableCache = false;

        $result = call_user_func($callback);

        $this->enableCache = $enabled;

        return $result;
    }

    /**
     * Retrieve multiple navigations by list of handles.
     */
    public function findNavigationsByHandle(array $handles): Collection
    {
        $menus = collect();

        $toFetch = collect();

        if ($this->shouldCache()) {
            foreach ($handles as $handle) {
                if ($menu = $this->getCachedNavigation($handle)) {
                    $menus->push($menu);
                } else {
                    $toFetch->push($handle);
                }
            }
        } else {
            $toFetch = collect($handles);
        }

        if ($toFetch->isNotEmpty()) {
            $result = $this->newMenuQuery()
                ->whereIn('handle', $handles)
                ->get()
                ->map(fn (Menu $menu) => $menu->toTree());

            foreach ($result as $menu) {
                if ($this->shouldCache()) {
                    $this->addNavigationToCache($menu);
                }

                $menus->push($menu);
            }
        }

        return $menus;
    }

    /**
     * Create new query builder for Menu.
     */
    protected function newMenuQuery(): QueryBuilder
    {
        return Menu::query()
            ->with('descendantsNew', function (DescendantsRelation $descendants) {
                $descendants->with('link', function (MorphOne $link) {
                    $link->with('resource', fn (MorphTo $resource) => $resource->morphWith(static::$morphLinkResourcesWith));
                });
            })
            ->root();
    }

    /**
     * Set which relations on the link resource should be loaded.
     */
    public static function loadLinkResourceWith(array $morphMap): void
    {
        static::$morphLinkResourcesWith = $morphMap;
    }
}
