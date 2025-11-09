<?php


namespace StackTrace\Navigation;


use Closure;
use Fureev\Trees\Config\Builder as TreeBuilder;
use Fureev\Trees\UseTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use StackTrace\Navigation\Concerns\HasLink;
use StackTrace\Translations\HasTranslations;

/**
 * @property string|null $handle
 * @property string|null $title
 * @property \StackTrace\Navigation\Link|null $link
 * @property array|null $meta
 * @property \Fureev\Trees\Collection<int, static> $descendants
 * @property static $parent
 *
 * @method static static make(array $attributes = [])
 */
class Menu extends Model implements HasMedia
{
    use HasLink,
        HasTranslations,
        InteractsWithMedia,
        SoftDeletes,
        UseTree;

    protected $guarded = false;

    /**
     * Callback for registering media collections.
     */
    protected static Closure|null $configureMediaCollectionsUsing = null;

    protected array $translatable = [
        'title',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Menu $model) {
            if (! $model->handle) {
                $model->handle = "me_".Str::random(28);
            }
        });
    }

    /**
     * Create tree from descendants.
     */
    public function toTree(): static
    {
        $this->setRelation('children', $this->descendants->toTree($this));

        return $this->unsetRelation('descendants');
    }

    public function registerMediaCollections(): void
    {
        if (static::$configureMediaCollectionsUsing instanceof Closure) {
            call_user_func(static::$configureMediaCollectionsUsing, $this);
        }
    }

    /**
     * Create new child menu.
     */
    public function createChild(array $attributes = [], array $options = []): static
    {
        $child = $this->makeChild($attributes);

        $child->save();

        return $child;
    }

    /**
     * Make a new child menu.
     */
    public function makeChild(array $attributes = []): static
    {
        $child = new static($attributes);

        $child->appendTo($this);

        return $child;
    }

    protected static function buildTree(): TreeBuilder
    {
        return TreeBuilder::defaultMulti();
    }

    /**
     * Make new instance of the menu, marking it as a root menu.
     */
    public static function makeAsRoot(array $attributes = []): static
    {
        $menu = static::make($attributes);

        $menu->makeRoot();

        return $menu;
    }

    /**
     * Create new instance of the menu, marking it as a root menu.
     */
    public static function createAsRoot(array $attributes = []): static
    {
        $menu = static::makeAsRoot($attributes);

        $menu->save();

        return $menu;
    }

    /**
     * Add custom configuration for media collections.
     */
    public static function configureMediaCollections(Closure $using): void
    {
        static::$configureMediaCollectionsUsing = $using;
    }
}
