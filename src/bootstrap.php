<?php

use Cockpit\Framework\AppFactory;
use Cockpit\Singleton\SingletonsModule;
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

    /** @var \Psr\Container\ContainerInterface $container */
    $container = $app->getContainer();
    $routeParser = $app->getRouteCollector()->getRouteParser();
    $container->set('router', $routeParser);
    $container->set(\Slim\Interfaces\RouteParserInterface::class, $routeParser);
    $container->set('basePath', $app->getBasePath());


    $authenticationMiddleware = $container->get(\Mezzio\Authentication\AuthenticationMiddleware::class);

    $modules = [
        new \Cockpit\App\CockpitModule($authenticationMiddleware),
        $container->get(\Cockpit\Collections\CollectionsModule::class),
        $container->get(SingletonsModule::class)
    ];
    $container->set('modules', $modules);

    foreach ($modules as $module) {
        $module->registerPaths(
            $container->get(\Cockpit\Framework\PathResolver::class),
            $container->get(\League\Plates\Engine::class)
        );

        $module->registerUI(
            $container->get(\Cockpit\App\UI\Menu::class),
            $container->get(\Cockpit\Framework\Template\PageAssets::class),
            $container->get(\Cockpit\Framework\EventSystem::class)
        );

        // Define routes
        $module->registerRoutes($app);
    }


    $app->addMiddleware($container->get(\Mezzio\Session\SessionMiddleware::class));
    $app->addMiddleware(new \Cockpit\Framework\Middlewares\JsonBodyParserMiddleware());
    $app->addMiddleware(new Middlewares\Whoops());

    return $app;
}
