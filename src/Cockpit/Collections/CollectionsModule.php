<?php

namespace Cockpit\Collections;

use Cockpit\App\UI\Menu;
use Cockpit\App\UI\MenuItem;
use Cockpit\Collections\Controller\Admin;
use Cockpit\Framework\EventSystem;
use Cockpit\Framework\PathResolver;
use Cockpit\Framework\Template\PageAssets;
use Cockpit\Module;
use Cockpit\Singleton\SingletonRepository;
use League\Plates\Engine;
use Mezzio\Authentication\AuthenticationMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class CollectionsModule implements Module
{
    /** @var CollectionRepository */
    private $collections;
    /** @var Engine */
    private $engine;
    /** @var AuthenticationMiddleware */
    private $authenticationMiddleware;

    public function __construct(CollectionRepository $collections, Engine $engine, AuthenticationMiddleware $authenticationMiddleware)
    {
        $this->collections = $collections;
        $this->engine = $engine;
        $this->authenticationMiddleware = $authenticationMiddleware;
    }

    public function registerUI(Menu $menu, PageAssets $assets, EventSystem $eventSystem): void
    {
        $menu->addMenuItem(new MenuItem('Collections', 'assets:collections/icon.svg', 'collections', false));

        $assets->addAsset('components', 'assets:collections/field-collectionlink.tag');
        $assets->addAsset('scripts', 'assets:collections/link-collectionitem.js');


        $eventSystem->on("admin.dashboard.widgets", [$this, 'installDashboardWidgets'], 100);
    }

    public function installDashboardWidgets($widgets)
    {
        $collections = $this->collections->byGroup(null, false);

        $widgets[] = [
            "name" => "collections",
            "content" => $this->engine->render("collections::views/widgets/dashboard", compact('collections')),
            "area" => 'aside-left'
        ];
    }

    public function registerRoutes(App $app)
    {
        $app->group(
            '/collections',
            function (RouteCollectorProxy $group) {
                $group->get('', Admin::class . ':index')->setName('collections');
                $group->get('/collection/{name:[0-9a-z\-]+}', Admin::class . ':collection')->setName('collection');
                $group->post('/save_collection', Admin::class . ':save_collection')->setName('save_collection');
                $group->post('/find', Admin::class . ':find')->setName('collections_find');
                $group->get('/entries/{name:[0-9a-z\-]+}', Admin::class . ':entries')->setName('collections_entries');
                $group->get('/entry/{name:[0-9a-z\-]+}', Admin::class . ':entry')->setName('collections_entry');
                $group->get('/entry/{name:[0-9a-z\-]+}/{id:[0-9a-z\-]+}', Admin::class . ':entry')->setName('collections_entry');
                $group->post('/save_entry/{name:[0-9a-z\-_]+}', Admin::class . ':save_entry')->setName('collections_save_entry');
                $group->post('/utils/getUserCollections', Admin::class . ':getUserCollections')->setName('collections_user_collections');
            }
        )->addMiddleware($this->authenticationMiddleware);
    }

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void
    {
        $this->engine->addFolder('collections', __DIR__);
    }
}
