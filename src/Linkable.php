<?php


namespace StackTrace\Navigation;


use Illuminate\Contracts\Routing\UrlRoutable;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait Linkable
{
    /**
     * The label of the link.
     */
    public function getLinkLabel(): string|array|null
    {
        return null;
    }

    /**
     * The location of the link.
     */
    public function getLinkLocation(): string|array|null
    {
        return null;
    }

    /**
     * The link route of the model.
     */
    public function getLinkRoute(): ?string
    {
        return null;
    }

    /**
     * Retrieve the route params of the link.
     */
    public function getLinkRouteParams(): array
    {
        return [];
    }

    /**
     * Determine if the link is internal.
     */
    public function isLinkExternal(): bool
    {
        return false;
    }

    /**
     * Create new link to the linkable model.
     */
    public function createLink(): ?Link
    {
        $link = $this->makeLink();
        $link?->save();
        return $link;
    }

    /**
     * Make a new link to the linkable model.
     */
    public function makeLink(): ?Link
    {
        $label = $this->getLinkLabel();

        if (! $label) {
            return null;
        }

        $route = $this->getLinkRoute();
        if ($route && $this instanceof UrlRoutable) {
            $link = new Link([
                'label' => $label,
                'route_name' => $route,
                'route_params' => $this->getLinkRouteParams(),
            ]);
            $link->resource()->associate($this);
            return $link;
        }

        if ($location = $this->getLinkLocation()) {
            $link = new Link([
                'label' => $label,
                'href' => $location,
                'is_external' => $this->isLinkExternal(),
            ]);
            $link->resource()->associate($this);
            return $link;
        }

        return null;
    }
}
