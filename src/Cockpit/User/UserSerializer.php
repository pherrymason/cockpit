<?php

namespace Cockpit\User;

use Cockpit\Framework\Authentication\User;

class UserSerializer
{
    public function serialize(User $user)
    {
        return [
            'id' => $user->id(),
            '_id' => $user->id(),
            'api_key' => '',
            'apiKey' => '',
            'password' => '',
            'email' => $user->getDetail('email'),
            'user' => $user->getDetail('user'),
            'name' => $user->getDetail('name'),
            'active' => (bool)$user->getDetail('active', false),
            'i18n' => 'en',
        ];
    }
}