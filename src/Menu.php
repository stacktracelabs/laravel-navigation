<?php


namespace StackTrace\Navigation;


use Closure;
use Fureev\Trees\Config\Base;
use Fureev\Trees\Contracts\TreeConfigurable;
use Fureev\Trees\NestedSetTrait;
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
 * @property \Fureev\Trees\Collection $descendantsNew
 *
 * @method static static make(array $attributes = [])
 */
class Menu extends Model implements TreeConfigurable, HasMedia
{
    use SoftDeletes, NestedSetTrait, HasTranslations, InteractsWithMedia, HasLink {
        NestedSetTrait::getCasts as getNestedSetTraitCasts;
    }

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

    public function getCasts(): array
    {
        return array_merge(
            parent::getCasts(),
            $this->getNestedSetTraitCasts(),
        );
    }

    protected static function booted()
    {
        static::creating(function (Menu $model) {
            if (! $model->handle) {
                $model->handle = "me_".Str::random(28);
            }
        });
    }

    public function toTree(): static
    {
        $this->setRelation('children', $this->descendantsNew->toTree($this));

        return $this;
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

    protected static function buildTreeConfig(): Base
    {
        return new Base(multi: true);
    }

    /**
     * Create new instance of the menu, marking it as a root menu.
     */
    public static function makeAsRoot(array $attributes = []): static
    {
        $menu = static::make($attributes);

        $menu->makeRoot();

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
