<?php

namespace App\Models\Authorization;
use App\Mail\LogsLoginCode;
use Cache;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Mail;

class LogsAuthGuard implements \Illuminate\Contracts\Auth\Guard
{
    const SESSION_LOGS_USERNAME_KEY = "logs_username";
    const CACHE_LOGS_PASSWORD_KEY = "logs_password";
    const SESSION_LOGS_AUTHORIZED_KEY = "logs_authorized";
    const SESSION_LOGS_AUTH_TRIES_KEY = "logs_authroization_tries";
    private UserProvider $userProvider;
    private Request $request;
    private Authenticatable|null $user;
    private bool $authorized = false;
    public function __construct(UserProvider $userProvider, Request $request)
    {
        $this->userProvider = $userProvider;
        $this->request = $request;
        $this->user = null;
        if (!is_null(session(self::SESSION_LOGS_AUTHORIZED_KEY)) && !is_null(session(self::SESSION_LOGS_USERNAME_KEY))) {
            $this->authorized = session(self::SESSION_LOGS_AUTHORIZED_KEY);
        }
        if (!is_null(session(self::SESSION_LOGS_USERNAME_KEY))) {
            $credentials = ["username" => session(self::SESSION_LOGS_USERNAME_KEY)];
            if (Cache::has(self::CACHE_LOGS_PASSWORD_KEY . ":" . $credentials["username"])) {
                $credentials["password"] = Cache::get(self::CACHE_LOGS_PASSWORD_KEY . ":" . $credentials["username"]);
            }
            $this->user = $this->userProvider->retrieveByCredentials($credentials);
            $this->sendPassword();
        }
    }

    /**
     * Initializes a new authorization providing an email address.
     * That email address has to be authorized with a logincode furtheron.
     * 
     * 
     * @param string $email
     * @return void
     */
    public function init(string $email)
    {
        $this->user = $this->userProvider->retrieveByCredentials(["username" => $email]);
        session([
            self::SESSION_LOGS_USERNAME_KEY => $this->user->getAuthIdentifier(),
        ]);
        $this->sendPassword();
    }
    /**
     * Checks if a logincode needs to be sent out to the user by mail
     * @return void
     */
    private function sendPassword()
    {
        // Store the password
        if (!$this->authorized && !empty($this->user->getAuthPassword()) && !Cache::has(self::CACHE_LOGS_PASSWORD_KEY . ":" . $this->user->getAuthIdentifier())) {
            Cache::put(self::CACHE_LOGS_PASSWORD_KEY . ":" . $this->user->getAuthIdentifier(), $this->user->getAuthPassword(), now()->addMinutes(5));
            // Send Email with login Code
            Mail::to($this->user->getAuthIdentifier())->send(new LogsLoginCode($this->user->getAuthPassword()));
        }
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        if (!$this->authorized || empty($this->user->getAuthPassword()))
            return false;
        else
            return $this->authorized;
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
            session([self::SESSION_LOGS_AUTHORIZED_KEY => false]);
            $this->authorized = false;
            return false;
        }

        if (!is_null($this->user) && $this->userProvider->validateCredentials($this->user, $credentials)) {
            session([self::SESSION_LOGS_AUTHORIZED_KEY => true]);
            $this->authorized = true;
            return true;
        } else {
            session([self::SESSION_LOGS_AUTHORIZED_KEY => false]);
            $tries = session(self::SESSION_LOGS_AUTH_TRIES_KEY, 0);
            if ($tries < 5) {
                session([self::SESSION_LOGS_AUTH_TRIES_KEY => ($tries + 1)]);
            } else {
                Cache::forget(self::CACHE_LOGS_PASSWORD_KEY . ":" . $this->user->getAuthIdentifier());
            }
            $this->authorized = false;
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