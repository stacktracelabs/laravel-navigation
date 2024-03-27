<?php


namespace StackTrace\Navigation;


use Illuminate\Database\Eloquent\Model;
use StackTrace\Translations\LocalizedString;

class Location
{
    /**
     * The query params.
     */
    protected string|array|null $queryParams = null;

    /**
     * The resource under the link.
     */
    protected ?Model $resource = null;

    public function __construct(
        public readonly string|LocalizedString|null $href,
        public readonly ?string $route,
        public readonly ?array $routeParams,
        public bool $external
    ) { }

    /**
     * Set query params on the location.
     */
    public function withQueryParams(string|array|null $params): static
    {
        $this->queryParams = $params;

        return $this;
    }

    /**
     * Set resource on the location.
     */
    public function withResource(?Model $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Retrieve query params set on the location.
     */
    public function getQueryParams(): array|string|null
    {
        return $this->queryParams;
    }

    /**
     * Retrieve the resource on the location.
     */
    public function getResource(): ?Model
    {
        return $this->resource;
    }

    /**
     * Create new location to route.
     */
    public static function toRoute(string $name, array $params = []): static
    {
        return new static(href: null, route: $name, routeParams: $params, external: false);
    }

    /**
     * Create new location to given URL.
     */
    public static function toUrl(string|LocalizedString $url, bool $external): static
    {
        return new static(href: $url, route: null, routeParams: null, external: $external);
    }

    /**
     * Create new internal location.
     */
    public static function internal(string|LocalizedString $url): static
    {
        return static::toUrl($url, external: false);
    }

    /**
     * Create new external location.
     */
    public static function external(string|LocalizedString $url): static
    {
        return static::toUrl($url, external: true);
    }
}
