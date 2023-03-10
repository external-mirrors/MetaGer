<?php

namespace App\Models\Authorization;

class Token extends Authorization
{

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
            $this->tokens[] = [
                "token" => $tokenString,
                "signature" => $tokenSignature,
                "date" => $tokenDate
            ];
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
     * @return mixed
     */
    public function getToken()
    {
    }
}