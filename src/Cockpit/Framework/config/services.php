<?php

use Cockpit\Framework\Authentication\MySQLUserRepository;
use Psr\Container\ContainerInterface;

return [
    /*
    \Cockpit\Framework\LexyRenderEngine::class => function (ContainerInterface $c) {
        return new \Cockpit\Framework\LexyRenderEngine();
    },*/

    // Authentication --------------
    \Mezzio\Authentication\UserRepositoryInterface::class => function (ContainerInterface $c) {
        $factory = $c->get(MySQLUserRepository::class);
    },

    \Mezzio\Authentication\AuthenticationMiddleware::class => function (ContainerInterface $c) {
        /** @var \Mezzio\Authentication\AuthenticationMiddlewareFactory $factory */
        $factory = $c->get(\Mezzio\Authentication\AuthenticationMiddlewareFactory::class);

        return $factory->__invoke($c);
    },

    \Mezzio\Authentication\AuthenticationInterface::class => function (ContainerInterface $c) {
        return new Cockpit\Framework\Authentication\Authentication();
    }
];

