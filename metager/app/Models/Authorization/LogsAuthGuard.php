<?php

namespace App\Models\Authorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class LogsAuthGuard implements \Illuminate\Contracts\Auth\Guard
{
    private UserProvider $userProvider;
    private Request $request;
    private Authenticatable|null $user;
    public function __construct(UserProvider $userProvider, Request $request)
    {
        $this->userProvider = $userProvider;
        $this->request = $request;
        $this->user = null;
    }
    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return !is_null($this->user);
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|string|null
     */
    public function id()
    {
        if (!is_null($this->user))
            return $this->user->getAuthIdentifier();
        return null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        if (empty($credentials["username"]) || empty($credentials["password"])) {
            return false;
        }
        $user = $this->userProvider->retrieveByCredentials($credentials);
        if (!is_null($user) && $this->userProvider->validateCredentials($user, $credentials)) {
            $this->setUser($user);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        return $this->check();
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;
        return $this;
    }
}