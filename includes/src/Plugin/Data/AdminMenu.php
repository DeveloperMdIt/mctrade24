<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

use Illuminate\Support\Collection;
use stdClass;

/**
 * Class AdminMenu
 * @package JTL\Plugin\Data
 */
class AdminMenu
{
    /**
     * @var Collection<stdClass>
     */
    private Collection $items;

    public function __construct()
    {
        $this->items = new Collection();
    }

    /**
     * @return Collection<stdClass>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Collection<stdClass> $items
     */
    public function setItems(Collection $items): void
    {
        $this->items = $items;
    }

    public function addItem(stdClass $item): void
    {
        $this->items->push($item);
    }

    public function removeItem(int $menuID): void
    {
        $this->items = $this->items->reject(static function ($value, $key) use ($menuID): bool {
            return $value->kPluginAdminMenu === $menuID;
        });
    }

    public function getItemByID(int $menuID): ?stdClass
    {
        return $this->items->firstWhere('kPluginAdminMenu', $menuID);
    }
}
