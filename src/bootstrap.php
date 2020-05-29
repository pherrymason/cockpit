<?php

use Cockpit\Framework\AppFactory;
use Cockpit\Singleton\Module;
use Slim\App;
use Slim\Handlers\Strategies\RequestResponseArgs;

include(dirname(__DIR__).'/vendor/autoload.php');

function getCockpitApp(array $configuration): App
{
    $app = AppFactory::createCockpit($configuration);

    $app->setBasePath(
        (function () {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            $uri = (string)parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
            if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
                return $_SERVER['SCRIPT_NAME'];
            }
            if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
                return $scriptDir;
            }
            return '';
        })()
    );


    $routeCollector = $app->getRouteCollector();
    $routeCollector->setDefaultInvocationStrategy(new RequestResponseArgs());
    $container = $app->getContainer();
    $routeParser = $app->getRouteCollector()->getRouteParser();
    $container->set('router', $routeParser);

    $modules = [
        new Module()
    ];


    foreach ($modules as $module) {
        $module->registerPaths(
            $container->get(\Cockpit\Framework\PathResolver::class),
            $container->get(\League\Plates\Engine::class)
        );

        // Define routes
        $module->registerRoutes($app);
    }



    $middleware = $container->get(\Mezzio\Authentication\AuthenticationMiddleware::class);
    $app->addMiddleware($middleware);
    //$app->addErrorMiddleware(true, true, true);
    $app->addMiddleware(new \Franzl\Middleware\Whoops\WhoopsMiddleware());

    return $app;
}
