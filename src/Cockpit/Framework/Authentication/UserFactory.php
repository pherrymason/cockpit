<?php

namespace Cockpit\Framework\Authentication;

use Mezzio\Authentication\UserInterface;
use Psr\Container\ContainerInterface;

class UserFactory
{
    public function __invoke(ContainerInterface $container) : callable
    {
        return function (string $identity, array $roles = [], array $details = []) : UserInterface {
            return new User($identity, $roles, $details);
        };
    }

    public static function create(string $identity, array $roles = [], $details = []): UserInterface
    {
        return new User($identity, $roles, $details);
    }
}