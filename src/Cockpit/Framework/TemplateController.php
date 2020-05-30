<?php

namespace Cockpit\Framework;

use Cockpit\App\UI\Menu;
use Cockpit\Framework\Template\PageAssets;
use League\Plates\Engine;
use Mezzio\Authentication\UserInterface;
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

            // not implemented
            'extract' => $this->sharedData($request),
            'components' => new \ArrayObject([]),    // vendor/raulferras/cockpit/modules/Cockpit/Helper/Admin.php:79
            'pageAssets' => $this->container->get(PageAssets::class),
            'menuModules' => $this->container->get(Menu::class)->items(
            ), // {menu.modules} vendor/raulferras/cockpit/modules/Cockpit/Helper/Admin.php:84
            'modules' => $this->container->get('modules'),
            'user' => $request->getAttributes()[UserInterface::class]
        ];

        return $data;
    }

    private function sharedData(RequestInterface $request)
    {
        $languages = $this->container->get('languages');
        $defaultLanguage = $this->container->get('defaultLanguage');
        /** @var UserInterface $user */
        $user = $request->getAttributes()[UserInterface::class];

        $sharedData = [
            'acl' => ['finder' => true],
            'appLanguages' => [],
            'defaultLanguage' => $defaultLanguage,
            'languageDefaultLabel' => $languages[$defaultLanguage],
            'groups' => ['admin' => true],
            'locale' => 'en',
            'site_url' => '/admin/',
            'user' => [
                'active' => '1',
                'data' => [],
                'email' => '',
                'group' => 'admin',
                'i18n' => 'en',
                'name' => $user->getIdentity(),
                'user' => $user->getIdentity()
            ]
        ];

        foreach ($languages as $code => $languageName) {
            $sharedData['appLanguages'][] = ['code' => $code, 'label' => $languageName];
            $sharedData['languages'][] = ['code' => $code, 'label' => $languageName];
        }


        return $sharedData;
    }
}
