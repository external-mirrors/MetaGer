<?php

namespace App\Models\Logs;
use Carbon;
use Request;

class LogsAccessKey
{
    public readonly int $id;
    public readonly string $name;
    public readonly string $key;
    public readonly Carbon $created_at;
    public readonly Carbon|null $accessed_at;

    public function __construct($access_key_data)
    {
        $this->id = $access_key_data->id;
        $this->name = $access_key_data->name;
        if (is_null(Request::old($this->name))) {
            $this->key = "********-****-****-****-************";
        } else {
            $this->key = Request::old($this->name);
        }
        $this->created_at = new Carbon($access_key_data->created_at, "UTC");
        if (is_null($access_key_data->accessed_at)) {
            $this->accessed_at = null;
        } else {
            $this->accessed_at = new Carbon($access_key_data->accessed_at, "UTC");
        }
    }
}