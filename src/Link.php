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
use StackTrace\Translations\LocalizedString;

/**
 * @property string|null $label
 * @property string|null $href
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

    protected $guarded = false;

    /**
     * List of translatable attributes.
     */
    protected array $translatable = [
        'label', 'href',
    ];

    /**
     * Casts of the model.
     */
    protected $casts = [
        'is_external' => 'boolean',
        'route_params' => 'array',
        'query_params' => 'array',
        'meta' => 'array',
    ];

    /**
     * The resource which is accessible under link.
     */
    public function resource(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The owner of the link.
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Set the query params as key value pairs on the link.
     */
    public function setQueryParams(array $params): static
    {
        $this->query_params = [
            'type' => 'array',
            'params' => $params,
        ];

        return $this;
    }

    /**
     * Set raw query params as string on the link.
     */
    public function setRawQueryParams(string $params): static
    {
        $this->query_params = [
            'type' => 'raw',
            'params' => $params,
        ];

        return $this;
    }

    /**
     * Determine if the link is external.
     */
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

    /**
     * Retrieve the URL of the link.
     */
    public function getUrl(): ?string
    {
        $base = $this->getBaseUrl();

        if ($base) {
            return $this->appendQueryParams($base);
        }

        return null;
    }

    /**
     * Create new link from location.
     */
    public static function createFromLocation(string|LocalizedString $label, Location $location, ?Model $linkable): static
    {
        $link = new static([
            'label' => $label,
            'href' => $location->href,
            'is_external' => $location->external,
            'route_name' => $location->route,
            'route_params' => $location->routeParams,
        ]);

        $query = $location->getQueryParams();
        if (is_string($query)) {
            $link->setRawQueryParams($query);
        } else if (is_array($query)) {
            $link->setQueryParams($query);
        }

        $link->linkable()->associate($linkable);
        $link->resource()->associate($location->getResource());

        $link->save();

        return $link;
    }
}
