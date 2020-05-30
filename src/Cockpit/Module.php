<?php

namespace Cockpit;

use Cockpit\App\UI\Menu;
use Cockpit\Framework\EventSystem;
use Cockpit\Framework\PathResolver;
use Cockpit\Framework\Template\PageAssets;
use League\Plates\Engine;
use Slim\App;

interface Module
{
    public function registerUI(Menu $menu, PageAssets $assets, EventSystem $eventSystem): void;
    public function registerRoutes(App $app);

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void;
}
