<?php

namespace App\Models\Authorization;

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