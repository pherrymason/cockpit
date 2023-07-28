<?php

namespace Cockpit\App;

use Cockpit\App\Controller\Accounts;
use Cockpit\App\Controller\Assets;
use Cockpit\App\Controller\Auth;
use Cockpit\App\Controller\Base;
use Cockpit\App\Controller\Utils;
use Cockpit\App\UI\Menu;
use Cockpit\Framework\PathResolver;
use Cockpit\Module;
use League\Plates\Engine;
use Mezzio\Authentication\AuthenticationMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class CockpitModule implements Module
{
    /** @var AuthenticationMiddleware */
    private $authenticationMiddleware;

    public function __construct(AuthenticationMiddleware $authenticationMiddleware)
    {
        $this->authenticationMiddleware = $authenticationMiddleware;
    }

    public function registerUI(Menu $menu, \Cockpit\Framework\Template\PageAssets $assets, \Cockpit\Framework\EventSystem $eventSystem): void
    {
        $assets->addAssets('scripts', [
            // polyfills
            'assets:polyfills/dom4.js',
            'assets:polyfills/document-register-element.js',
            'assets:polyfills/URLSearchParams.js',

            // libs
            'assets:lib/moment.js',
            'assets:lib/jquery.js',
            'assets:lib/lodash.js',
            'assets:lib/riot/riot.js',
            'assets:lib/riot/riot.bind.js',
            'assets:lib/riot/riot.view.js',
            'assets:lib/uikit/js/uikit.min.js',
            'assets:lib/uikit/js/components/notify.min.js',
            'assets:lib/uikit/js/components/tooltip.min.js',
            'assets:lib/uikit/js/components/lightbox.min.js',
            'assets:lib/uikit/js/components/sortable.min.js',
            'assets:lib/uikit/js/components/sticky.min.js',
            'assets:lib/mousetrap.js',
            'assets:lib/storage.js',
            'assets:lib/i18n.js',

            // app
            'assets:app/js/app.js',
            'assets:app/js/app.utils.js',
            'assets:app/js/codemirror.js',
            'assets:app/components/cp-actionbar.js',
            'assets:app/components/cp-fieldcontainer.js',
            'assets:cockpit/components.js',
            'assets:cockpit/cockpit.js',

            // uikit components
            'assets:lib/uikit/js/components/autocomplete.min.js',
            'assets:lib/uikit/js/components/tooltip.min.js',

            // app related
            'assets:app/js/bootstrap.js'
        ]);

        $assets->addAsset('styles', 'assets:app/css/style.css');
    }

    public function registerRoutes(App $app)
    {
        // Auth
        $app->map(['GET','POST'], '/auth/login', Auth::class.':login')->setName('login');
        $app->get('/auth/logout', Auth::class.':logout')->setName('logout');

        $app->group('', function (RouteCollectorProxy $group) {
            $group->get('/', Base::class.':dashboard')->setName('home');

            // Accounts
            $group->group('/accounts', function (RouteCollectorProxy $group) {
                $group->get('', Accounts::class.':index')->setName('accounts');
                $group->get('/account', Accounts::class.':account')->setName('accounts_account');

                $group->get('/account/{uid:[0-9]+}', Accounts::class.':account')->setName('accounts_account');
                $group->map(['GET','POST'],'/find', Accounts::class.':find')->setName('accounts_find');
                $group->get('/create', Accounts::class.':create')->setName('accounts_create');
                $group->post('/save', Accounts::class.':save')->setName('accounts_save');
                $group->post('/remove', Accounts::class.':remove')->setName('accounts_remove');
            });

            // Assets
            $group->group('/assetsmanager', function (RouteCollectorProxy $group) {
                $group->get('', Assets::class.':index')->setName('assets');
                $group->post('/listAssets', Assets::class.':listAssets')->setName('assets_list');
                $group->post('/addFolder', Assets::class.':addFolder')->setName('assets_folder');
                $group->post('/renameFolder', Assets::class.':renameFolder')->setName('assets_folder');
                $group->post('/removeFolder', Assets::class.':removeFolder')->setName('assets_folder');
                $group->post('/upload', Assets::class.':upload')->setName('assets_upload');
                $group->post('/asset/{id:[0-9\-a-z]+}', Assets::class.':asset')->setName('assets_asset');
                $group->post('/updateAsset', Assets::class.':updateAsset')->setName('assets_asset');
                $group->post('/_folders', Assets::class.':_folders')->setName('assets_folders');
                $group->post('/removeAssets', Assets::class.':removeAssets')->setName('assets_folders');
            });

            // Utils
            $group->post('/cockpit/utils/revisionsCount', Utils::class.':revisionsCount')->setName('utils_revisionsCount');
        })->addMiddleware($this->authenticationMiddleware);
    }

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void
    {
        $engine->addFolder('cockpit', __DIR__);
    }
}
