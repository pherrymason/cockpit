<?php

namespace Cockpit\Framework\Authentication;

use Mezzio\Authentication\UserInterface;

class User implements UserInterface
{
    /** @var string */
    private $identity;

    /** @var string[] */
    private $roles;

    /** @var array */
    private $details;

    public function __construct(string $identity, array $roles = [], array $details = [])
    {
        $this->identity = $identity;
        $this->roles = $roles;
        $this->details = $details;
    }

    public function getIdentity() : string
    {
        return $this->identity;
    }

    public function getRoles() : array
    {
        return $this->roles;
    }

    public function getDetails() : array
    {
        return $this->details;
    }

    /**
     * @param mixed $default Default value to return if no detail matching
     *     $name is discovered.
     * @return mixed
     */
    public function getDetail(string $name, $default = null)
    {
        return $this->details[$name] ?? $default;
    }

    public function id()
    {
        return $this->details['id'];
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }
}