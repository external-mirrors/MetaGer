<?php

namespace App\Authentication;

use Arr;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class KeyUserProvider implements UserProvider
{
    public function retrieveById($identifier): KeyUser
    {
        return new KeyUser($identifier);
    }

    public function retrieveByToken($identifier, $token): KeyUser
    {
        return new KeyUser($identifier);
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials): KeyUser|null
    {
        if (isset($credentials['key'])) {
            return new KeyUser($credentials['key']);
        }
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        // In this case, the key itself is the credential
        if ($user instanceof KeyUser && $user->getAuthIdentifier() === Arr::get($credentials, 'key', '')) {
            return true;
        }
        return false;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // KeyUser does not use password hashing, so this method can be empty
        return;
    }
}