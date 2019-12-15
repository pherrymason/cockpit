<?php

use Psr\Container\ContainerInterface;

return [
    \Cockpit\App\Revisions::class => function(ContainerInterface $c) {
        return new \Cockpit\App\Revisions(
            $c->get('dbal.mysql')
        );
    }
];
