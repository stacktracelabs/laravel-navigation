<?php


namespace StackTrace\Navigation\Concerns;


use Illuminate\Database\Eloquent\Relations\MorphOne;
use StackTrace\Navigation\Link;
use StackTrace\Navigation\Location;
use StackTrace\Translations\LocalizedString;

/**
 * @property-read Link|null $link
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasLink
{
    public function link(): MorphOne
    {
        return $this->morphOne(Link::class, 'linkable');
    }

    /**
     * Retrieve the model link.
     */
    public function getLink(): ?Link
    {
        return $this->link;
    }

    /**
     * Set link on the model.
     */
    public function setLink(string|LocalizedString $label, Location $location): static
    {
        $this->removeLink();

        Link::createFromLocation($label, $location, $this);

        return $this;
    }

    /**
     * Remove link from the model.
     */
    public function removeLink(): static
    {
        $this->link?->delete();

        return $this;
    }
}
