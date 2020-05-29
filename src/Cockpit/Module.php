<?php

namespace Cockpit;

use Cockpit\Framework\PathResolver;
use League\Plates\Engine;
use Slim\App;

interface Module
{
    public function registerRoutes(App $app);

    public function registerPaths(PathResolver $pathResolver, Engine $engine): void;
}
