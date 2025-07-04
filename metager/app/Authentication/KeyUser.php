<?php

namespace App\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;

class KeyUser implements Authenticatable
{
    /**
     * The key associated with the user.
     *
     * @var string
     */
    public string $key;

    /**
     * Create a new KeyUser instance.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getAuthIdentifierName(): string
    {
        return 'key';
    }

    public function getAuthIdentifier(): string
    {
        return $this->key;
    }

    public function getAuthPasswordName(): string
    {
        return 'key';
    }

    public function getAuthPassword(): string
    {
        return $this->key;
    }

    public function getRememberToken(): string
    {
        return ''; // KeyUser does not use remember tokens
    }

    public function setRememberToken($value): void
    {
        // KeyUser does not use remember tokens
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}