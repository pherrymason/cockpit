<?php

namespace Cockpit\App\UI;

class Menu
{
    /** @var MenuItem[] */
    private $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function addMenuItem(MenuItem $item)
    {
        $this->items[] = $item;
    }

    public function items(): array
    {
        return $this->items;
    }
}
