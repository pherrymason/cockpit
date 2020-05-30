<?php

namespace Cockpit\Collections;

use Cockpit\App\UI\Menu;
use Cockpit\App\UI\MenuItem;
use Cockpit\Framework\PathResolver;
use Cockpit\Module;
use League\Plates\Engine;
use Slim\App;

final class CollectionsModule implements Module
{
    public function registerUI(Menu $menu, \Cockpit\Framework\Template\PageAssets $assets): void
    {
        $menu->addMenuItem(new MenuItem('\'Collections\'', 'assets:collections/icon.svg', 'collections', false));

        $assets->addAsset('components', 'assets:collections/field-collectionlink.tag');
        $assets->addAsset('scripts', 'assets:collections/link-collectionitem.js');
    }

    public function registerRoutes(App $app)
    {
        // TODO: Implement registerRoutes() method.
    }

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void
    {
        // TODO: Implement registerPaths() method.
    }
}
