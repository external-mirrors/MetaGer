<?php

namespace App\Models\Authorization;
use Illuminate\Contracts\Auth\Authenticatable;
use DB;

class LogsUser implements Authenticatable
{
    private $email;
    private string $login_token;
    private int $discount;


    public function fetchUserByCredentials(array $credentials)
    {
        $user = DB::table("logs_user")->where("email", "=", $credentials["username"])->first();
        $this->email = $credentials["username"];
        if (!is_null($user)) {
            if (isset($credentials["password"])) {
                $this->login_token = $credentials["password"];
            } else {
                $this->login_token = (string) rand(0, 999999);
                while (strlen($this->login_token) < 6) {
                    $this->login_token = '0' . $this->login_token;
                }
            }
            $this->discount = $user->discount;
        } else {
            $this->login_token = "";
        }
        return $this;

    }
    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'email';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the name of the password attribute for the user.
     *
     * @return string
     */
    public function getAuthPasswordName()
    {
        return 'login_token';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->{$this->getAuthPasswordName()};
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        return null;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return null;
    }
}