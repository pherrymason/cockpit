<?php

namespace Cockpit\Framework;

use League\Plates\Engine;
use Psr\Container\ContainerInterface;

class TemplateEngineFactory
{
    public function create(ContainerInterface $c)
    {
        $templates = new \League\Plates\Engine(dirname(__DIR__)  . '/App/views');

        $globalData = [];
        $templates->addData($globalData);

        $this->registerFunctions($templates, $c);

        return $templates;
    }

    private function registerFunctions(Engine $engine, ContainerInterface $c)
    {
        $engine->registerFunction('route', function (string $routeName, array $data = [], array $queryParams = []) use ($c) {
            $routeParser = $c->get('router');

            return $routeParser->urlFor($routeName, $data, $queryParams);
        });

        $engine->registerFunction('asset', function ($resource) {
            return $resource;
        });

        $engine->registerFunction('lang', function (string $msg) {
           return $msg;
        });

        $engine->registerFunction('hasAccess', function ($resource, string $action) {
            return true;
        });

        $engine->registerFunction('isSuperAdmin', function () {
            return true;
        });

        $engine->registerFunction('trigger', function ($eventName, $data) use ($c) {
            $event = $c->get(EventSystem::class);
            $event->trigger($eventName, $data);
        });


    }
}
