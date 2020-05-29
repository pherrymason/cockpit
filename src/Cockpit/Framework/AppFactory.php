<?php

namespace Cockpit\Framework;

use DI\ContainerBuilder;
use Slim\App;

class AppFactory extends \Slim\Factory\AppFactory
{
    public static function createCockpit(array $configuration)
    {
        $services = require(dirname(__DIR__, 2) . '/config/services.php');
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);
        $builder->addDefinitions($services);
//        $builder->enableCompilation($configuration['paths']['#tmp']);
        $container = $builder->build();

        return new App(
            self::determineResponseFactory(),
            $container,
            static::$callableResolver,
            static::$routeCollector,
            static::$routeResolver,
            static::$middlewareDispatcher
        );
    }
}
