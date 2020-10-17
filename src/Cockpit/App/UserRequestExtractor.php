<?php

namespace Cockpit\App;

use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ServerRequestInterface;

trait UserRequestExtractor
{
    protected function extractUser(ServerRequestInterface $request)
    {
        return $request->getAttribute(UserInterface::class);
    }
}