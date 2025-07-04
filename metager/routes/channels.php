<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Authorization.Key.{key}', function ($user, $key) {
    return Auth::guard("key")->attempt(["key" => $key, "cost" => 0]);
}, [
    'guards' => ['key'],
]);
