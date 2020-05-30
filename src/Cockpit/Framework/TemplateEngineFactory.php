<?php

namespace Cockpit\Framework;

use League\Plates\Engine;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouteCollectorInterface;

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
        $engine->registerFunction('route', function (?string $routeName, array $data = [], array $queryParams = []) use ($c) {
            $routeParser = $c->get('router');
//            $routeParser = $c->get(RouteCollectorInterface::class);

            try {
                return $routeParser->urlFor($routeName ?? '', $data, $queryParams);
            } catch (\Exception $e) {
                return '#';
            }

        });

        $engine->registerFunction('asset', function ($resource) {
            return $resource;
        });

        $engine->registerFunction('lang', function (?string $msg) {
           return $msg;
        });

        $engine->registerFunction('base', function (?string $url) use ($c) {
            if (strpos($url, ':')===false) {
                return $c->get('basePath') . $url;
            }

            if ($url !== null) {
                [$what, $path] = explode(':', $url);
                return $c->get('basePath') . '/' . $what . '/' . $path;
            }

            return $c->get('basePath');
        });

        $engine->registerFunction('hasAccess', function ($resource, string $action) {
            return true;
        });

        $engine->registerFunction('isSuperAdmin', function () {
            return true;
        });

        $engine->registerFunction('trigger', function ($eventName, $data = []) use ($c) {
            $event = $c->get(EventSystem::class);
            $event->trigger($eventName, $data);
        });


    }
}
