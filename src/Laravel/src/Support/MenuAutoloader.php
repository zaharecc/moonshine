<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Support;

use Closure;
use Leeto\FastAttributes\Attributes;
use MoonShine\Contracts\Core\CrudPageContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\ResourceContract;
use MoonShine\Contracts\MenuManager\MenuAutoloaderContract;
use MoonShine\Contracts\MenuManager\MenuElementContract;
use MoonShine\Contracts\MenuManager\MenuFillerContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\MenuManager\Attributes\CanSee;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;

/**
 * @phpstan-type PSMenuItem array{
 *      filler: class-string<MenuFillerContract>,
 *      canSee: null|string,
 *  }
 *
 * @phpstan-type PSMenuGroup array{
 *       label: string,
 *       class: class-string<MenuFillerContract>,
 *       icon: string|null,
 *       canSee: null|string,
 *       translatable: bool,
 *   }
 *
 * @phpstan-type PSMenu array{
 *      string,
 *      PSMenuItem|array{
 *           group: PSMenuGroup,
 *           items: list<PSMenuItem>,
 *      }
 *  }
 */
final readonly class MenuAutoloader implements MenuAutoloaderContract
{
    /**
     * @param  MoonShine  $core
     */
    public function __construct(private CoreContract $core) {}

    /**
     * @return PSMenu
     */
    public function toArray(): array
    {
        $items = [];

        $resolveItems = static function (
            MenuFillerContract $item,
            &$items,
        ): void {
            $skip = Attributes::for($item, SkipMenu::class)->class();

            if (! \is_null($skip->first())) {
                return;
            }

            $group = Attributes::for($item, Group::class)->class()->first();
            $canSee = Attributes::for($item, CanSee::class)->class()->first();

            $label = $group?->label;
            $icon = $group?->icon;

            $namespace = get_class($item);
            $data = ['filler' => $namespace, 'canSee' => $canSee?->method];

            if ($label !== null) {
                $existsGroup = $items[$label] ?? null;
                $items[$label] = [
                    'group' => ['class' => $namespace, 'label' => $label, 'icon' => $icon, 'canSee' => $canSee?->method, 'translatable' => $group?->translatable],
                    'items' => \is_null($existsGroup)
                        ? [$data]
                        : [
                            ...$existsGroup['items'],
                            $data,
                        ],
                ];

                return;
            }

            $items[$namespace] = $data;
        };

        foreach ($this->core->getResources()->toArray() as $item) {
            $resolveItems($item, $items);
        }

        $excludePages = static fn(PageContract $page) => ! $page instanceof CrudPageContract;

        foreach ($this->core->getPages()->filter($excludePages)->toArray() as $item) {
            $resolveItems($item, $items);
        }

        return $items;
    }

    /**
     * @param  PSMenu|null  $cached
     *
     * @return MenuElementContract[]
     */
    public function resolve(?array $cached = null): array
    {
        return $this->generateMenu($cached ?? $this->toArray());
    }

    /**
     * @param  PSMenu|list<PSMenuItem>  $data
     *
     * @return list<MenuElementContract>
     */
    private function generateMenu(array $data): array
    {
        $menu = [];

        foreach ($data as $item) {
            if (isset($item['group'])) {
                $group = $item['group'];
                $menu[] = MenuGroup::make(
                    $group['translatable'] ? __($group['label']) : $group['label'],
                    $this->generateMenu($item['items']),
                    $group['icon'],
                )->when($group['canSee'], fn(MenuGroup $ctx) => $ctx->canSee($this->canSee($group['class'], $group['canSee'])));
                continue;
            }

            $menu[] = $this->toMenuItem(...$item);
        }

        return $menu;
    }

    /**
     * @param  class-string<MenuFillerContract&(PageContract|ResourceContract)>  $filler
     */
    private function toMenuItem(string $filler, ?string $icon = null, ?string $canSee = null): MenuItem
    {
        $resolved = app($filler);
        $label = $resolved->getTitle();

        return MenuItem::make($label, $filler, $icon)
            ->when($canSee, fn(MenuItem $item) => $item->canSee($this->canSee($resolved, $canSee)));
    }

    /**
     * @param  MenuFillerContract|class-string<MenuFillerContract>  $filler
     */
    private function canSee(string|MenuFillerContract $filler, string $method): Closure
    {
        if(is_string($filler)) {
            $filler = app($filler);
        }

        return static fn() => $filler->{$method}();
    }
}
