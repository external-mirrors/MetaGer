<?php

namespace App\Authentication;

use App\Authentication\KeyUser;
use App\Authentication\KeyUserProvider;
use Cookie;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\StatefulGuard;
use Request;

class KeyAuthGuard implements StatefulGuard
{
    use GuardHelpers;

    protected $lastAttempted;
    protected string $login_method = 'query'; // Default to query parameter

    public function __construct(KeyUserProvider $provider)
    {
        $this->provider = $provider;
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $key = "";
        if (Cookie::has('key')) {
            $key = Cookie::get('key');
            $this->login_method = "cookie";
        }
        if (Request::hasHeader("key")) {
            $key = Request::header("key");
            $this->login_method = "header"; // Header takes precedence over cookie
        }
        if (Request::filled('key')) {
            $key = Request::input('key');
            $this->login_method = "query"; // Query parameter takes precedence over header and cookie
        }

        if ($key === "") {
            return null; // No key provided
        } else {
            return $this->user = $this->provider->retrieveById($key);
        }
    }

    public function validate(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);
        // Validate the credentials against the key
        return $this->hasValidCredentials($user, $credentials);
    }

    function attempt(array $credentials = [], $remember = false): bool
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);
            return true;
        }
        return false;
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    function once(array $credentials = [])
    {
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }
        return false;
    }

    function onceUsingId($id)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);

            return $user;
        }

        return false;
    }

    function viaRemember()
    {
        // Key-based authentication does not support "remember me" functionality
        return false;
    }



    function login(\Illuminate\Contracts\Auth\Authenticatable $user, $remember = false)
    {
        $this->user = $user;
    }

    function logout()
    {
        $this->user = null;
        if ($this->login_method === "cookie") {
            Cookie::queue(Cookie::forget('key'));
        }
    }

    function loginUsingId($id, $remember = false): bool|KeyUser
    {
        $user = $this->provider->retrieveById($id);
        if ($user !== null) {
            $this->login($user, $remember);
            return $this->user;
        }
        return false;
    }


}