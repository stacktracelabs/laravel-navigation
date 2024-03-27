<?php


namespace StackTrace\Navigation;


use Closure;
use Fureev\Trees\Contracts\TreeConfigurable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MenuGenerator
{
    protected ?Closure $linkFactory = null;

    protected ?Closure $menuFactory = null;

    protected ?Closure $resolveChildrenUsing = null;

    protected int $depth = 1;

    /**
     * Set the max depth of the generator.
     */
    public function depth(int $depth): static
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * Resolve children from given source.
     */
    protected function resolveChildren(mixed $source): Collection
    {
        if ($this->resolveChildrenUsing instanceof Closure) {
            return call_user_func($this->resolveChildrenUsing, $source);
        }

        if ($source instanceof Model && $source instanceof TreeConfigurable) {
            return $source->children;
        }

        return collect();
    }

    /**
     * Set factory for link creation.
     */
    public function createLinksWith(?Closure $closure): static
    {
        $this->linkFactory = $closure;

        return $this;
    }

    /**
     * Set factory for menu creation.
     */
    public function createMenuWith(?Closure $closure): static
    {
        $this->menuFactory = $closure;

        return $this;
    }

    /**
     * Create new link for the source.
     */
    protected function createLink(mixed $source): ?Link
    {
        if ($this->linkFactory instanceof Closure) {
            return call_user_func($this->linkFactory, $source);
        }

        return null;
    }

    /**
     * Create Menu item from given source.
     */
    protected function createMenu(mixed $source, ?Menu $parentMenu = null, mixed $parentSource = null): Menu
    {
        $menu = new Menu();
        if ($parentMenu) {
            $menu->appendTo($parentMenu);
        }
        call_user_func($this->menuFactory, $menu, $source, $parentMenu, $parentSource);
        $link = $this->createLink($source);
        $link?->save();
        $menu->link()->associate($link);
        $menu->save();

        return $menu;
    }

    /**
     * Generate menu from given source.
     */
    public function generate(mixed $source): Menu
    {
        if (is_null($this->menuFactory)) {
            throw new \RuntimeException("The menu factory is not set.");
        }

        $menu = $this->createMenu($source);

        $this->createChildMenu($source, $menu);

        return $menu;
    }

    /**
     * Create a new child menu.
     */
    protected function createChildMenu(mixed $parent, Menu $parentMenu, int $depth = 0): void
    {
        // Stop when final depth is reached.
        if ($depth >= $this->depth) {
            return;
        }

        $children = $this->resolveChildren($parent);

        foreach ($children as $child) {
            $menu = $this->createMenu($child, $parentMenu, $parent);

            $this->createChildMenu($child, $menu, $depth + 1);
        }
    }

    /**
     * Create new menu generator.
     */
    public static function make(): static
    {
        return new static();
    }
}
