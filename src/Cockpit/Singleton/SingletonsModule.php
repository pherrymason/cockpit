<?php

namespace Cockpit\Singleton;

use Cockpit\App\UI\Menu;
use Cockpit\App\UI\MenuItem;
use Cockpit\Framework\PathResolver;
use Cockpit\Singleton\Controller\Admin;
use League\Plates\Engine;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Cockpit\Module;

final class SingletonsModule implements Module
{
    /** @var SingletonRepository */
    private $singletons;
    /** @var Engine */
    private $engine;

    public function __construct(SingletonRepository $singletons, Engine $engine)
    {
        $this->singletons = $singletons;
        $this->engine = $engine;
    }

    public function registerUI(Menu $menu, \Cockpit\Framework\Template\PageAssets $assets, \Cockpit\Framework\EventSystem $eventSystem): void
    {
        $menu->addMenuItem(new MenuItem('Singletons', 'assets:singletons/icon.svg', 'singletons', false));


        $eventSystem->on('admin.dashboard.widgets', [$this, 'installDashboardWidgets'], 100);
    }

    public function installDashboardWidgets($widgets)
    {
        $singletons = $this->singletons->byGroup('x');

        $widgets[] = [
            'name' => 'singleton',
            'content' => $this->engine->render('singletons::views/widgets/dashboard', compact('singletons')),
            'area' => 'aside-right'
        ];
    }

    public function registerRoutes(App $app)
    {
        $app->group(
            '/singletons',
            function (RouteCollectorProxy $group) {
                $group->map(['GET'], '', Admin::class . ':index')->setName('singletons');
                $group->get('/singleton', Admin::class.':singleton')->setName('singletons-singleton');
                $group->get('/form/{name:[a-z0-9]+}', Admin::class . ':form')->setName('singleton');

                $group->get('/{name:[a-z0-9]+}', Admin::class . ':singleton')->setName('singleton-structure');

                $group->get('/revisions/{name:[a-z0-9]+}/{id:[a-z0-9]+}', Admin::class . ':revisions')->setName(
                    'singleton-revisions'
                );
                $group->post('/update_data/{name:[a-z0-9]+}', Admin::class . ':update_data')->setName(
                    'singleton-update_data'
                );
            }
        );
    }

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void
    {
        $pathResolver->setPath('singletons', __DIR__);
        $engine->addFolder('singletons', __DIR__);
    }
}
