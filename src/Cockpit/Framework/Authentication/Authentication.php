<?php

namespace Cockpit\Framework\Authentication;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\DefaultUser;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Authentication implements AuthenticationInterface
{
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        //@TODO
        $details = [
            'id' => '1'
        ];
        return new DefaultUser('admin', [], $details);
    }

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement unauthorizedResponse() method.

    }
}
