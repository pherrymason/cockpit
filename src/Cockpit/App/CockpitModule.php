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
    public function registerUI(Menu $menu, \Cockpit\Framework\Template\PageAssets $assets): void
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
        $app->get('/', Base::class.':dashboard')->setName('home');
    }

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void
    {
        $engine->addFolder('cockpit', __DIR__);
    }
}
