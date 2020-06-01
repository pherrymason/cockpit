<?php

use Cockpit\Framework\Authentication\MySQLUserRepository;
use Mezzio\Authentication\Session\PhpSession;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouteParserInterface;

return [
    \Mezzio\Session\SessionMiddleware::class => function (ContainerInterface $c) {
        $sessionPersistence = new \Mezzio\Session\Ext\PhpSessionPersistence(false, true);

        return new \Mezzio\Session\SessionMiddleware($sessionPersistence);
    },

    // Authentication --------------
    \Mezzio\Authentication\UserRepositoryInterface::class => function (ContainerInterface $c) {

        $userFactory = $c->get(\Mezzio\Authentication\DefaultUserFactory::class)->__invoke($c);

        return new MySQLUserRepository(
            $c->get(\Doctrine\DBAL\Connection::class),
            $userFactory
        );
    },

    PhpSession::class => function (ContainerInterface $c) {
        $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
        /** @var RouteParserInterface $route */
        $route = $c->get('router');
        $userFactory = $c->get(\Mezzio\Authentication\DefaultUserFactory::class);
        return new PhpSession(
            $c->get(\Mezzio\Authentication\UserRepositoryInterface::class),
            [
                'redirect' => '/admin/auth/login',
            ],
            [$responseFactory, 'createResponse'],
            $userFactory($c)
        );
    },

    \Mezzio\Authentication\AuthenticationMiddleware::class => function (ContainerInterface $c) {
        /** @var \Mezzio\Authentication\AuthenticationMiddlewareFactory $factory */
        $factory = $c->get(\Mezzio\Authentication\AuthenticationMiddlewareFactory::class);

        return $factory->__invoke($c);
    },

    \Mezzio\Authentication\AuthenticationInterface::class => DI\get(PhpSession::class),
/*
    \Mezzio\Authentication\AuthenticationInterface::class => function (ContainerInterface $c) {
        return new Cockpit\Framework\Authentication\Authentication(
            $c->get(\Mezzio\Authentication\UserRepositoryInterface::class)
        );
    }*/
];

