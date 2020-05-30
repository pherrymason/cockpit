<?php

namespace Cockpit;

use Cockpit\App\UI\Menu;
use Cockpit\Framework\PathResolver;
use League\Plates\Engine;
use Slim\App;

interface Module
{
    public function registerUI(Menu $menu): void;
    public function registerRoutes(App $app);

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void;
}
