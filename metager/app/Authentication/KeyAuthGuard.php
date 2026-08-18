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
    public string $login_method = 'query'; // Default to query parameter

    /**
     * Whether user() has already worked out who this is.
     *
     * Needed as well as $user because the answer is very often *nobody*, and
     * GuardHelpers only memoises a hit: `is_null($this->user)` cannot tell "not
     * looked yet" from "looked, and there is no key". So every Auth::check(),
     * every Auth::guest() and every @auth in a blade used to go back to the
     * cookie jar, the headers and the query string for an anonymous visitor —
     * sixteen times over in Searchengines::__construct alone, which asks once
     * per configured engine.
     */
    private bool $resolved = false;

    public function __construct(KeyUserProvider $provider)
    {
        $this->provider = $provider;
    }

    public function user()
    {
        if ($this->resolved || !is_null($this->user)) {
            return $this->user;
        }

        $this->resolved = true;

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
            if (Request::hasHeader("anonymous-token-key")) {
                // This is a header login like any other; without saying so it would keep the
                // 'query' default and be mistaken for one — which makes the SafeBrowse link in
                // layouts/result.blade.php hand out the key that the client already injects as a
                // header on its own, putting it in access logs for no gain.
                $this->login_method = "header";
                $user = $this->provider->retrieveById(Request::header("anonymous-token-key"));
                $user->temporary = true; // Mark as temporary user
                return $this->user = $user;
            }
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
        // And stays logged out. Cookie::forget only *queues* the deletion for
        // the response, so the cookie is still readable for the rest of this
        // request — without this the next user() call would find it and log the
        // visitor straight back in.
        $this->resolved = true;
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