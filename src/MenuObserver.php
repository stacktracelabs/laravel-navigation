<?php


namespace StackTrace\Navigation;


class MenuObserver
{
    public function updated(Menu $menu): void
    {
        // TODO: Invalidate menu cache
    }

    public function deleted(Menu $menu): void
    {
        // TODO: Invalidate menu cache
    }

    public function forceDeleted(Menu $menu): void
    {
        // TODO: Invalidate menu cache
    }
}
