<?php

namespace App\Models\Logs;
use App\Models\Authorization\LogsUser;
use Auth;

class LogsAccountProvider
{
    private LogsUser $user;
    public readonly LogsClient $client;
    public readonly LogsAbo|null $abo;
    public function __construct()
    {
        $this->user = Auth::guard("logs")->user();
        $this->client = new LogsClient($this->user->getAuthIdentifier());
        try {
            $this->abo = new LogsAbo($this->client->contact->email);
        } catch (\Exception $e) {
            $this->abo = null;
        }
    }
}