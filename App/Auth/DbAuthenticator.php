<?php

namespace App\Auth;

use App\Models\User;
use Framework\Auth\SessionAuthenticator;
use Framework\Core\IIdentity;

class DbAuthenticator extends SessionAuthenticator
{
    protected function authenticate(string $username, string $password): ?IIdentity
    {
        $user = User::login($username, $password);

        if ($user) {
            return new DbIdentity($user->id, $user->email, $user->meno . ' ' . $user->priezvisko);
        }

        return null;
    }
}
