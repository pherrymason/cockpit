<?php

namespace Cockpit\User;

use Cockpit\Framework\Authentication\User;

interface UserRepository
{
    public function byId($id): ?User;

    public function byUser(string $user): ?User;

    public function byEmail(string $email): ?User;

    public function save(array $data);
}