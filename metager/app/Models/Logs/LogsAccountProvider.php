<?php

namespace App\Models\Logs;
use App\Models\Authorization\LogsUser;
use Auth;

class LogsAccountProvider
{
    private LogsUser $user;
    public readonly LogsClient $client;
    public readonly LogsAbo|null $abo;
    public function __construct(?string $email = null)
    {
        if (is_null($email)) {
            $this->client = new LogsClient(Auth::guard("logs")->user()->getAuthIdentifier());
        } else {
            $this->client = new LogsClient($email);
        }
        try {
            $this->abo = new LogsAbo($this->client->contact->email);
        } catch (\Exception $e) {
            $this->abo = null;
        }

    }
}