<?php

namespace App\Models\Authorization;

class TokenAuthorization extends Authorization
{

    /**
     * @var Token[]
     */
    private $tokens = [];

    public function __construct($tokenString)
    {
        parent::__construct();

        $tokenJson = json_decode($tokenString);
        if ($tokenJson === null || !is_array($tokenJson)) {
            $this->availableTokens = 0;
        }

        foreach ($tokenJson as $token) {
            if (!property_exists($token, "token") || !property_exists($token, "date") || !property_exists($token, "signature")) {
                continue;
            }
            $tokenString = $token->token;
            if (!is_string($tokenString)) {
                continue;
            }
            $tokenSignature = $token->signature;
            if (!is_string($tokenSignature)) {
                continue;
            }
            $tokenDate = $token->date;
            if (!is_string($tokenDate)) {
                continue;
            }
            $this->tokens[] = new Token($tokenString, $tokenSignature, $tokenDate);
        }
        $this->availableTokens = sizeof($this->tokens);
    }

    /**
     * @return mixed
     */
    public function authenticate()
    {
    }

    /**
     * @return Token[]
     */
    public function getToken()
    {
        return $this->tokens;
    }
}

class Token
{
    /**
     * @var string $token
     * @var string $signature
     * @var string $date
     */
    public $token, $signature, $date;
    /**
     * @param string $token
     * @param string $signature
     * @param string $date
     */
    public function __construct($token, $signature, $date)
    {
        $this->token = $token;
        $this->signature = $signature;
        $this->date = $date;
    }
}