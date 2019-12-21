<?php

use Psr\Container\ContainerInterface;

return [
    // Repositories
    \Cockpit\Singleton\SingletonRepository::class => function (ContainerInterface $c) {
        return new \Cockpit\Singleton\DBSingletonRepository(
            $c->get('dbal.mysql')
        );
    },

    // Controllers
    \Cockpit\Singleton\Controller\Admin::class => function (ContainerInterface $c) {
        return new \Cockpit\Singleton\Controller\Admin(
            $c->get(\Cockpit\Singleton\SingletonRepository::class),
            $c->get('app')
        );
    }
];
