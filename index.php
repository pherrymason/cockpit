<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Autoload vendor libs
use DI\ContainerBuilder;

include(__DIR__ . '/lib/vendor/autoload.php');

$configuration = require(__DIR__ . '/src/Sepia/CMS/config/config.php');
$services = require(__DIR__ . '/src/services.php');

// Container configuration
$builder = new ContainerBuilder();
$builder->useAnnotations(false);
$builder->addDefinitions($services);
//        $builder->enableCompilation($configuration['paths']['#tmp']);
$container = $builder->build();

$appPath = __DIR__;
$publicPath = __DIR__;
$app = new \Cockpit\App($container, $appPath, $publicPath, $configuration, \Cockpit\App::MODE_HTTP);
$app->boot();
$app->run();
