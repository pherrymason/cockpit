<?php

namespace Cockpit\Framework;

use Cockpit\App\UI\Menu;
use League\Plates\Engine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class TemplateController
{
    /** @var Engine */
    protected $templateEngine;
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    public function __construct(Engine $templateEngine, \Psr\Container\ContainerInterface $container)
    {
        $this->templateEngine = $templateEngine;
        $this->container = $container;
    }

    public function renderResponse(RequestInterface $request, ResponseInterface $response, string $view, array $data = [], array $globalData = [])
    {
        if (count($globalData)) {
            $this->templateEngine->addData($globalData);
        }

        $template = $this->templateEngine->make($view);
        $template->layout('layouts/app', $this->layoutData($request));

        $response->getBody()->write($template->render($data));

        return $response;
    }

    private function layoutData(RequestInterface $request): array
    {
        $data = [
            'appName' => $this->container->get('app.name'),
            'i18n' => $this->container->get('i18n'),
            'route' => $request->getAttributes()['__routingResults__']->getUri(),
            'scripts' => $this->scripts(),
            'styles' => $this->styles(),

            // not implemented
            'extract' => [],
            'components' => new \ArrayObject([]),    // vendor/raulferras/cockpit/modules/Cockpit/Helper/Admin.php:79

            'menuModules' => $this->container->get(Menu::class)->items(), // {menu.modules} vendor/raulferras/cockpit/modules/Cockpit/Helper/Admin.php:84
            'modules' => $this->container->get('modules')
        ];

        return $data;
    }

    private function scripts(): array {
        return [
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
        ];
    }

    private function styles(): array
    {
        return [

            'assets:app/css/style.css',
        ];
    }
}
