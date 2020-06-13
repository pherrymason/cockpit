<?php

namespace Cockpit\Framework;

use Cockpit\App\UI\Menu;
use Cockpit\Framework\Template\PageAssets;
use Laminas\Diactoros\Response\HtmlResponse;
use League\Plates\Engine;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class TemplateController
{
    /** @var Engine */
    protected $templateEngine;
    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    public function __construct(Engine $templateEngine, \Psr\Container\ContainerInterface $container)
    {
        $this->templateEngine = $templateEngine;
        $this->container = $container;
    }

    public function renderResponseLayout(RequestInterface $request, string $view, array $data = [], array $globalData = []): HtmlResponse
    {
        if (count($globalData)) {
            $this->templateEngine->addData($globalData);
        }

        $this->templateEngine->addData($this->layoutData($request));

        return new HtmlResponse($this->templateEngine->render($view, $data));
    }

    public function renderResponse(RequestInterface $request, string $view, array $data = [], array $globalData = []): HtmlResponse
    {
        if (count($globalData)) {
            $this->templateEngine->addData($globalData);
        }

        $this->templateEngine->addData($this->layoutData($request));

        $template = $this->templateEngine->make($view);
        $template->layout('layouts/app');

        return new HtmlResponse($template->render($data));
    }

    private function layoutData(RequestInterface $request): array
    {
        $user = $request->getAttribute(UserInterface::class);
        $data = [
            'appName' => $this->container->get('app.name'),
            'i18n' => $this->container->get('i18n'),
            'route' => $request->getAttribute('__routingResults__')->getUri(),

            // not implemented
            'extract' => $this->sharedData($request),
            'components' => new \ArrayObject([]),    // vendor/raulferras/cockpit/modules/Cockpit/Helper/Admin.php:79
            'pageAssets' => $this->container->get(PageAssets::class),
            'menuModules' => $this->container->get(Menu::class)->items(), // {menu.modules} vendor/raulferras/cockpit/modules/Cockpit/Helper/Admin.php:84
            'modules' => $this->container->get('modules'),
            'user' => $user
        ];

        return $data;
    }

    private function sharedData(RequestInterface $request)
    {
        $languages = $this->container->get('languages');
        $defaultLanguage = $this->container->get('defaultLanguage');
        /** @var UserInterface $user */
        $user = $request->getAttribute(UserInterface::class);

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
                'name' => $user !== null ? $user->getIdentity() : null,
                'user' => $user !== null ? $user->getIdentity() : null
            ]
        ];

        foreach ($languages as $code => $languageName) {
            $sharedData['appLanguages'][] = ['code' => $code, 'label' => $languageName];
            $sharedData['languages'][] = ['code' => $code, 'label' => $languageName];
        }


        return $sharedData;
    }
}
