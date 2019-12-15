<?php

use Psr\Container\ContainerInterface;

return [
    // Controllers
    \Cockpit\Collections\Controller\Admin::class => function (ContainerInterface $c) {
        return new \Cockpit\Collections\Controller\Admin(
            $c->get(\Cockpit\Collections\DBCollectionRepository::class),
            $c->get(\Cockpit\Collections\DBEntriesRepository::class),
            $c->get('app')
        );
    },

    \Cockpit\Collections\MySQLCollectionTableManager::class => function (ContainerInterface $c) {
        return new \Cockpit\Collections\MySQLCollectionTableManager($c->get('dbal.mysql'));
    },

    // Repositories
    \Cockpit\Collections\DBCollectionRepository::class => function (ContainerInterface $c) {
        return new \Cockpit\Collections\DBCollectionRepository(
            $c->get('dbal.mysql'),
            $c->get(\Cockpit\Collections\MySQLCollectionTableManager::class)
        );
    },

    \Cockpit\Collections\DBEntriesRepository::class => function (ContainerInterface $c) {
        return new \Cockpit\Collections\DBEntriesRepository(
            $c->get('dbal.mysql'),
            $c->get(\Cockpit\Collections\MySQLCollectionTableManager::class)
        );
    }
];
