<?php

namespace Cockpit\Framework\Middlewares;

use Cockpit\Framework\SentryWhoopsHandler;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
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

    /**
     * Ensure the Sentry handler runs for every error Whoops handles, so real
     * errors are reported to Sentry (deprecations are handled separately in
     * process(), where they are reported and silenced before reaching Whoops).
     */
    protected function createWhoopsInstance(ServerRequestInterface $request): Run
    {
        $whoops = parent::createWhoopsInstance($request);
        $whoops->prependHandler($this->logHandler);

        return $whoops;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // parent::process() registers Whoops as the global error handler, which
        // turns *every* PHP error -- deprecations included -- into a fatal page
        // that aborts the request. Third-party libraries not yet PHP 8.4 ready
        // (e.g. league/flysystem) emit such deprecations. We wrap the request in
        // our own handler, registered AFTER Whoops so it takes precedence: each
        // deprecation is reported to Sentry and silenced, while every other error
        // level is delegated back to Whoops so real errors still surface.
        $deprecationLevels = E_DEPRECATED | E_USER_DEPRECATED;
        $innerHandler = function (ServerRequestInterface $request) use ($handler, $deprecationLevels): ResponseInterface {
            $sentry = $this->logHandler;
            $whoopsHandler = set_error_handler(
                function (int $level, string $message, string $file = '', int $line = 0) use ($deprecationLevels, $sentry, &$whoopsHandler) {
                    if (($level & $deprecationLevels) === 0) {
                        // Not a deprecation: hand back to Whoops (the handler that
                        // was active when we registered ours).
                        return $whoopsHandler ? ($whoopsHandler)($level, $message, $file, $line) : false;
                    }

                    // Respect the @-operator / error_reporting().
                    if ((error_reporting() & $level) === 0) {
                        return true;
                    }

                    try {
                        $sentry->setException(new \ErrorException($message, 0, $level, $file, $line));
                        $sentry->handle();
                    } catch (\Throwable) {
                        // Reporting must never break the request because of a deprecation.
                    }

                    // Handled: do not propagate, print, or abort the flow.
                    return true;
                }
            );

            try {
                return $handler->handle($request);
            } finally {
                restore_error_handler();
            }
        };

        // Delegate to Whoops' process() but with our handler wrapping the request,
        // so our set_error_handler() is installed after Whoops' own register().
        return parent::process($request, new class($innerHandler) implements RequestHandlerInterface {
            /** @var callable */
            private $callback;

            public function __construct(callable $callback)
            {
                $this->callback = $callback;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return ($this->callback)($request);
            }
        });
    }
}
