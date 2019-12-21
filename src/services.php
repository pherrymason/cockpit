<?php

use Psr\Container\ContainerInterface;

/**
 * @var array $configuration
 */

$services = [
    'dbal.mysql' => function (ContainerInterface $c) {
        $config = new \Doctrine\DBAL\Configuration();
        $params = $c->get('database.config');
        $connectionParams = [
            'driver' => 'pdo_mysql',
            'host' => $params['server'],
            'dbname' => $params['options']['db'],
            'user' => $params['options']['user'],
            'password' => $params['options']['password']
        ];

        return \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    },

    'sql' => function (ContainerInterface $c) {
        $config = $c->get('database.config');
        return new \Framework\Database\MySQLStorage(
            $config['server'],
            $config['options']['db'] ?? null,
            $config['options']['user'] ?? null,
            $config['options']['password'] ?? null
        );
    },

    'mongolite' => function (ContainerInterface $c) {
        $config = $c->get('database.config');

        return new \Framework\Database\MongoLite\MongoLite($config['server'], $config['options']);
    },


    'mongo' => function (ContainerInterface $c) {
        $config = $c->get('database.config');

        return new \Framework\Database\MongoDB\Mongo($config['server'], $config['options'], $config['driverOptions']);
    },

    // nosql storage
    'storage' => function (ContainerInterface $c) {
        $config = $c->get('database.config');
        $service = $c->get($config['driver']);

        if (!$service instanceof \Framework\Database\DatabaseConnection) {
            throw new \RuntimeException('Invalid database driver selected.');
        }

        return $service;
    },

    'path' => function (ContainerInterface $c) {
        return new \Framework\PathResolver($c->get('paths'), $c->get('site_url'), $c->get('docs_root'));
    },

    'events' => function (ContainerInterface $c) {
        return new \Framework\EventSystem();
    },

    'filestorage' => function (ContainerInterface $c) {
        $pathResolver = $c->get('path');
        $customConfig = $c->get('filestorage.config');
        $storages = array_replace_recursive([
            'root' => [
                'adapter' => 'League\Flysystem\Adapter\Local',
                'args' => [$pathResolver->path('#root:')],
                'mount' => true,
                'url' => $pathResolver->pathToUrl('#root:', true)
            ],

            'site' => [
                'adapter' => 'League\Flysystem\Adapter\Local',
                'args' => [$pathResolver->path('site:')],
                'mount' => true,
                'url' => $pathResolver->pathToUrl('site:', true)
            ],

            'tmp' => [
                'adapter' => 'League\Flysystem\Adapter\Local',
                'args' => [$pathResolver->path('#tmp:')],
                'mount' => true,
                'url' => $pathResolver->pathToUrl('#tmp:', true)
            ],

            'thumbs' => [
                'adapter' => 'League\Flysystem\Adapter\Local',
                'args' => [$pathResolver->path('#thumbs:')],
                'mount' => true,
                'url' => $pathResolver->pathToUrl('#thumbs:', true)
            ],

            'uploads' => [
                'adapter' => 'League\Flysystem\Adapter\Local',
                'args' => [$pathResolver->path('#uploads:')],
                'mount' => true,
                'url' => $pathResolver->pathToUrl('#uploads:', true)
            ],

            'assets' => [
                'adapter' => 'League\Flysystem\Adapter\Local',
                'args' => [$pathResolver->path('#uploads:')],
                'mount' => true,
                'url' => $pathResolver->pathToUrl('#uploads:', true)
            ]

        ], $customConfig);

        $events = $c->get('events');
        $events->trigger('cockpit.filestorages.init', [&$storages]);

        return new FileStorage($storages);
    },

    'memory' => function (ContainerInterface $c) {
        $config = $c->get('memory.config');
        return new SimpleStorage\Client($config['server'], $config['options']);
    },

    'mailer' => function (ContainerInterface $c) {
        $config = $c->get('mailer.config');
        return new \Mailer($config['transport'] ?? 'mail', $config);
    },

    'acl' => function (ContainerInterface $c) {
        return new \Lime\Helper\SimpleAcl();
    },

    'assets' => function (ContainerInterface $c) {
        return new \Lime\Helper\Assets();
    },

    'fs' => function (ContainerInterface $c) {
        return new \Lime\Helper\Filesystem($c->get('path'));
    },

    'image' => function (ContainerInterface $c) {
        return new \Lime\Helper\Image();
    },
    'user.language' => function (ContainerInterface $c) {
        $default = 'en';
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return $default;
        }
        return \strtolower(\substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    },
    'i18n' => function (ContainerInterface $c) {
        return new\Lime\Helper\I18n($c->get('user.language'));
    },
    'utils' => function (ContainerInterface $c) {
        return new\Lime\Helper\Utils();
    },
    'coockie' => function (ContainerInterface $c) {
        return new\Lime\Helper\Cookie();
    },
    'yaml' => function (ContainerInterface $c) {
        return new\Lime\Helper\YAML();
    },
    'session' => function (ContainerInterface $c) {
        return new \Lime\Session($c->get('session.name'));
    },
    'cache' => function (ContainerInterface $c) {
        return new \Lime\Cache($c->get('path'), $c->get('app.name'));
    },

    'renderer' => function (ContainerInterface $c) {
        $renderer = new \Lexy();

        //register app helper functions
        $renderer->extend(function ($content) {

            $replace = [
                'extend' => '<?php $extend(expr); ?>',
                'base' => '<?php $app->base(expr); ?>',
                'route' => '<?php $app->route(expr); ?>',
                'trigger' => '<?php $app->trigger(expr); ?>',
                'assets' => '<?php echo $app->assets(expr); ?>',
                'start' => '<?php $app->start(expr); ?>',
                'end' => '<?php $app->end(expr); ?>',
                'block' => '<?php $app->block(expr); ?>',
                'url' => '<?php echo $app->pathToUrl(expr); ?>',
                'view' => '<?php echo $app->view(expr); ?>',
                'render' => '<?php echo $app->view(expr); ?>',
                'include' => '<?php echo include($app->path(expr)); ?>',
                'lang' => '<?php echo $app("i18n")->get(expr); ?>',
            ];

            $content = \preg_replace_callback('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', function ($match) use ($replace) {

                if (isset($match[3], $replace[$match[1]]) && \trim($match[1])) {
                    return \str_replace('(expr)', $match[3], $replace[$match[1]]);
                }

                return $match[0];

            }, $content);


            return $content;
        });

        return $renderer;
    }
];


$cockpitServices = require('App/config/services.php');
$collectionServices = require('Collections/config/services.php');
$singletonServices = require('Singleton/config/services.php');

return array_merge($configuration, $services, $cockpitServices, $collectionServices, $singletonServices);