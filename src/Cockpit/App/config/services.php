<?php

use Psr\Container\ContainerInterface;

return [
    \Cockpit\App\Revisions::class => function(ContainerInterface $c) {
        return new \Cockpit\App\Revisions(
            $c->get('dbal.mysql')
        );
    },

    \League\Flysystem\AdapterInterface::class => function (ContainerInterface $c) {
        return new \League\Flysystem\Adapter\Local($c->get('root_path'));
    },

    \League\Flysystem\Filesystem::class => function (ContainerInterface $c) {
        return new \League\Flysystem\Filesystem(
            $c->get(\League\Flysystem\AdapterInterface::class)
        );
    },

    \Cockpit\App\Assets\Uploader::class => function (ContainerInterface $c) {


        return new \Cockpit\App\Assets\Uploader(
            $c->get(\League\Flysystem\Filesystem::class),
            $c->get('path'),
            $c->get(\Cockpit\App\Assets\AssetRepository::class),
            $c->get(\Cockpit\App\Assets\FolderRepository::class),
            $c->get(\Cockpit\Framework\EventSystem::class),
            new \Cocur\Slugify\Slugify(),
            ['*'],
            30000000
            );
    },

    \Cockpit\App\Assets\Thumbnail::class => function () {
        return new \Cockpit\App\Assets\Thumbnail();
    },

    // Repositories -----------------------------------------------
    \Cockpit\App\Revisions\RevisionsRepository::class => function(ContainerInterface $c) {
        return new \Cockpit\App\Revisions\RevisionsRepository($c->get('dbal.mysql'));
    },

    \Cockpit\App\Assets\AssetRepository::class => function (ContainerInterface $c) {
        return new \Cockpit\App\Assets\DBAssetRepository(
            $c->get('dbal.mysql')
        );
    },

    \Cockpit\App\Assets\FolderRepository::class => function (ContainerInterface $c) {
        return new \Cockpit\App\Assets\DBFolderRepository(
            $c->get('dbal.mysql')
        );
    },

    // Controllers -----------------------------------------------
    \Cockpit\App\Controller\Assets::class => function (ContainerInterface $c) {
        return new \Cockpit\App\Controller\Assets(
            $c->get('app'),
            $c->get(\Cockpit\App\Assets\AssetRepository::class),
            $c->get(\Cockpit\App\Assets\FolderRepository::class),
            $c->get(\Cockpit\App\Assets\Uploader::class)
        );
    },

    \Cockpit\App\Controller\Utils::class => function (ContainerInterface $c) {
        return new \Cockpit\App\Controller\Utils(
            $c->get(\Cockpit\App\Revisions\RevisionsRepository::class),
            $c->get(\Cockpit\App\Assets\Thumbnail::class)
        );
    }
];
