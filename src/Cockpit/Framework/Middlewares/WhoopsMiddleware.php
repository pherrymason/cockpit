<?php

namespace Cockpit\Framework\Middlewares;

use Cockpit\Framework\SentryWhoopsHandler;
use Psr\Http\Message\ServerRequestInterface;
use Whoops\Run;

class WhoopsMiddleware extends \Middlewares\Whoops
{
    /** @var SentryWhoopsHandler */
    private $logHandler;

    public function __construct(SentryWhoopsHandler $logHandler)
    {
        $this->logHandler = $logHandler;
        parent::__construct();
    }

    protected function getWhoopsInstance(ServerRequestInterface $request): Run
    {
        $whoops = parent::getWhoopsInstance($request);

        $whoops->prependHandler($this->logHandler);

        return $whoops;
    }
}
