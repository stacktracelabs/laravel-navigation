<?php


namespace StackTrace\Navigation\Concerns;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use StackTrace\Navigation\Link;
use StackTrace\Navigation\Location;
use StackTrace\Translations\LocalizedString;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Link> $links
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasLinks
{
    public function links(): MorphMany
    {
        return $this->morphMany(Link::class, 'linkable');
    }

    /**
     * Retrieve list of links.
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    /**
     * Add a link to the model.
     */
    public function addLink(string|LocalizedString $label, Location $location): static
    {
        Link::createFromLocation($label, $location, $this);

        return $this;
    }
}
