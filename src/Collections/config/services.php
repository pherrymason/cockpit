<?php

use Psr\Container\ContainerInterface;

return [
    // Controllers
    \Cockpit\Collections\Controller\Admin::class => function (ContainerInterface $c) {
        return new \Cockpit\Collections\Controller\Admin(
            $c->get(\Cockpit\Collections\DBCollectionRepository::class),
            $c->get('app')
        );
    },




    // Repositories
    \Cockpit\Collections\DBCollectionRepository::class => function (ContainerInterface $c) {
        return new \Cockpit\Collections\DBCollectionRepository($c->get('dbal.mysql'));
    }
];
