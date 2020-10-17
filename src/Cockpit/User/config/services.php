<?php

return [
    \Cockpit\User\UserRepository::class => function (\Psr\Container\ContainerInterface $c) {
        return new \Cockpit\User\MySqlUserRepository($c->get('dbal.mysql'));
    }
];
