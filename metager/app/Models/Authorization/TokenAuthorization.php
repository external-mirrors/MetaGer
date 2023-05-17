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
        if ($tokenJson === null) {
            $this->availableTokens = 0;
            $tokenJson = [];
        } else if (!is_array($tokenJson)) {
            $this->availableTokens = 0;
            $tokenJson = [];
        } else {
            $this->availableTokens = sizeof($tokenJson);
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
        if (sizeof($this->tokens) > 0) {
            $this->availableTokens = sizeof($this->tokens);
        }
    }

    function makePayment(int $cost)
    {
        if (!$this->canDoAuthenticatedSearch()) {
            return false;
        }

        $tokens_to_use = [];
        for ($i = 0; $i < $cost; $i++) {
            $tokens_to_use[] = array_shift($this->tokens);
        }

        $url = $this->keyserver . "/token/use";
        $result_hash = md5($url . microtime(true));
        $mission = [
            "resulthash" => $result_hash,
            "url" => $url,
            "useragent" => "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:81.0) Gecko/20100101 Firefox/81.0",
            "headers" => [
                "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
                "Content-Type" => "application/json",
            ],
            "cacheDuration" => 0,
            "name" => "Key Login",
            "proxy" => false,
            // Don't use Http Proxy if defined in .env
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_POSTFIELDS => json_encode(["tokens" => $tokens_to_use])
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $this->usedTokens += sizeof($tokens_to_use);
        $this->updateCookie();

        return true;
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
        if (sizeof($this->tokens) === 0) {
            Cookie::queue(Cookie::forget("tokens", "/", null));
        } else {
            Cookie::queue(Cookie::forever("tokens", json_encode($this->tokens), "/", null, true, true));
        }
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
                return $new_tokens;
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