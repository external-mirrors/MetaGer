<?php

namespace App\Models\Authorization;

use Cookie;
use Illuminate\Support\Facades\Redis;

class TokenAuthorization extends Authorization
{

    /**
     * @var Token[]
     */
    private $tokens = [];
    private $keyserver = "";

    public function __construct($tokenString)
    {
        parent::__construct();
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";

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
        $this->checkTokens();
        $this->availableTokens = sizeof($this->tokens);
    }

    /**
     * @return mixed
     */
    public function authenticate()
    {
        if (!$this->canDoAuthenticatedSearch()) {
            return false;
        }
        $url = $this->keyserver . "/token/use";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . config("metager.metager.keymanager.access_token"),
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT => 5,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(["tokens" => $this->tokens]),
            CURLOPT_USERAGENT => "MetaGer"
        ]);

        $result = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response_code === 201) {
            $this->usedTokens = sizeof($this->tokens);
            Cookie::queue(Cookie::forget("tokens", "/"));
            return true;
        } elseif ($response_code === 422) {
            $result = json_decode($result);
            if ($result === null) {
                return false;
            }
            $this->parseError($result);
        }
    }

    /**
     * @return Token[]
     */
    public function getToken()
    {
        return $this->tokens;
    }

    private function checkTokens()
    {
        if (sizeof($this->tokens) === 0) {
            return false;
        }
        $url = $this->keyserver . "/token/check";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . config("metager.metager.keymanager.access_token"),
                "Content-Type: application/json"
            ],
            CURLOPT_TIMEOUT => 5,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(["tokens" => $this->tokens]),
            CURLOPT_USERAGENT => "MetaGer"
        ]);

        $result = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response_code === 200) {
            return true;
        } elseif ($response_code === 422) {
            $result = json_decode($result);
            if ($result === null) {
                return false;
            }
            $this->parseError($result);
        }
        return false;
    }

    private function updateCookie()
    {
        Cookie::queue(Cookie::forever("tokens", json_encode($this->tokens), "/", null, true, true));
    }

    private function parseError($result)
    {
        foreach ($result->errors as $error) {
            if ($error->msg === "Invalid Signatures") {
                // One or more tokens are invalid. Remove the invalid tokens
                $new_tokens = [];
                foreach ($error->value as $error_token) {
                    if ($error_token->status === "ok") {
                        $new_tokens[] = new Token($error_token->token, $error_token->signature, $error_token->date);
                    }
                }
                $this->tokens = $new_tokens;
                $this->updateCookie();
            }
        }
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