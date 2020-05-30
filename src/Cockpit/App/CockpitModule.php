<?php

namespace Cockpit\App;

use Cockpit\App\Controller\Base;
use Cockpit\App\UI\Menu;
use Cockpit\Framework\PathResolver;
use Cockpit\Module;
use League\Plates\Engine;
use Slim\App;

final class CockpitModule implements Module
{
    public function registerUI(Menu $menu): void
    {
        // TODO: Implement registerUI() method.
    }

    public function registerRoutes(App $app)
    {
        $app->get('/', Base::class.':dashboard')->setName('home');
    }

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void
    {
        $engine->addFolder('cockpit', __DIR__);
    }
}
