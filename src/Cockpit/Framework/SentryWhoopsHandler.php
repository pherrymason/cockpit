<?php

namespace Cockpit\Framework;

use Psr\Log\LoggerInterface;
use Sentry\State\HubInterface;
use Whoops\Exception\Inspector;
use Whoops\Handler\HandlerInterface;
use Whoops\RunInterface;

class SentryWhoopsHandler implements HandlerInterface
{
    /** @var RunInterface */
    private $run;
    /** @var Inspector $inspector */
    private $inspector;
    /** @var \Throwable $exception */
    private $exception;
    /** @var LoggerInterface */
    private $logger;
    /** @var HubInterface */
    private $sentry;

    public function __construct(LoggerInterface $logger, HubInterface $sentry)
    {
        $this->logger = $logger;
        $this->sentry = $sentry;
    }

    public function handle()
    {
        $exception = $this->exception;
        $this->logger->error($exception->getMessage());
        $this->sentry->captureException($exception);
    }

    public function setRun(RunInterface $run)
    {
        $this->run = $run;
    }

    public function setException($exception)
    {
        $this->exception = $exception;
    }

    public function setInspector(Inspector $inspector)
    {
        $this->inspector = $inspector;
    }
}
