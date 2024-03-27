<?php


namespace StackTrace\Navigation;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use StackTrace\Translations\HasTranslations;

/**
 * @property string|null $label
 * @property string|null $href
 * @property boolean $is_localized
 * @property boolean $is_external
 * @property \Illuminate\Database\Eloquent\Model|null $resource
 * @property \Illuminate\Database\Eloquent\Model|null $linkable
 * @property string|null $route_type
 * @property string|null $route_name
 * @property array|null $route_params
 * @property array|null $query_params
 * @property array|null $meta
 */
class Link extends Model
{
    use SoftDeletes, HasTranslations;

    /**
     * List of supported locales.
     */
    protected static array $locales = ['sk', 'en'];

    protected $guarded = false;

    protected array $translatable = [
        'label', 'href',
    ];

    /**
     * List of relations which should be loaded when accessing resource.
     */
    protected static array $morphResourceWith = [];

    protected $casts = [
        'is_localized' => 'boolean',
        'is_external' => 'boolean',
        'route_params' => 'array',
        'query_params' => 'array',
        'meta' => 'array',
    ];

    public function resource(): MorphTo
    {
        return $this->morphTo()->morphWith(static::$morphResourceWith);
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function setQueryParams(array $params): static
    {
        $this->query_params = [
            'type' => 'array',
            'params' => $params,
        ];

        return $this;
    }

    public function setRawQueryParams(string $params): static
    {
        $this->query_params = [
            'type' => 'raw',
            'params' => $params,
        ];

        return $this;
    }

    public function isExternal(): bool
    {
        return $this->is_external;
    }

    protected function collectHrefs()
    {
        return collect($this->getTranslations('href'))
            ->filter(fn ($value) => is_string($value) && !empty($value));
    }

    protected function resolveResourceKey(): string|int|null
    {
        if ($this->resource) {
            return $this->resource->getRouteKey();
        }

        return null;
    }

    protected function resolveRouteParams(): array
    {
        $params = $this->route_params;

        if (is_array($params)) {
            if (in_array(':key', $params)) {
                $key = $this->resolveResourceKey();

                return collect($params)->map(fn ($value) => $value == ':key' ? $key : $value)->all();
            }

            return $params;
        }

        return [];
    }

    protected function getBaseUrl(): ?string
    {
        $hrefs = $this->collectHrefs();

        if ($hrefs->isNotEmpty()) {
            if (! $this->is_localized) {
                return $hrefs->first();
            }

            return $hrefs->get(App::getLocale(), fn () => $hrefs->get(App::getFallbackLocale()));
        }

        if ($this->route_name && Route::has($this->route_name)) {
            $params = $this->resolveRouteParams() ?: [];

            return route($this->route_name, $params);
        }

        return null;
    }

    protected function appendQueryParams(string $url): string
    {
        if ($this->query_params) {
            $type = Arr::get($this->query_params, 'type');
            $params = Arr::get($this->query_params, 'params');

            if ($type == 'raw' && is_string($params)) {
                if (Str::contains($url, '?')) {
                    if (Str::endsWith($url, '&')) {
                        $url = rtrim($url, '&');
                    }

                    if (Str::endsWith($url, '?')) {
                        $url = rtrim($url, '?');
                    }

                    return $url.'&'.ltrim($params, '&');
                } else {
                    return $url.'?'.ltrim($params, '&');
                }
            }
        }

        return $url;
    }

    public function getUrl(): ?string
    {
        $base = $this->getBaseUrl();

        if ($base) {
            return $this->appendQueryParams($base);
        }

        return null;
    }

    public static function toRoute(string|array $label, string $routeName, array $routeParams = []): static
    {
        $link = new static([
            'label' => $label,
            'route_name' => $routeName,
            'route_params' => $routeParams,
        ]);

        $link->save();

        return $link;
    }

    public static function internal(string|array $label, string|array|null $url): static
    {
        $link = new static([
            'label' => $label,
            'href' => $url,
            'is_localized' => is_array($url),
        ]);

        $link->save();

        return $link;
    }

    public static function external(string|array $label, string|array|null $url): static
    {
        $link = new static([
            'label' => $label,
            'href' => $url,
            'is_external' => true,
            'is_localized' => is_array($url),
        ]);

        $link->save();

        return $link;
    }

    /**
     * Add relations which should be loaded with resource.
     */
    public static function loadResourceWith(array $morphMap = []): void
    {
        static::$morphResourceWith = $morphMap;
    }

    /**
     * Set available locales for the link.
     */
    public static function locales(array $locales): void
    {
        static::$locales = $locales;
    }

    /**
     * Retrieve available locales for the link.
     */
    public static function getLocales(): array
    {
        return static::$locales;
    }
}
