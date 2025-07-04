<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.Authorization.Key.{key}', function ($user, $key) {
    try {
        $key_data = Crypt::decrypt($key);
    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        return false;
    }

    if ((new Carbon\Carbon($key_data['expiration']))->isPast()) {
        return false;
    }

    if (!$user instanceof Authenticatable) {
        return false;
    }

    return Auth::guard("key")->validate([
        'key' => $key,
    ]);

}, [
    'guards' => ['key'],
]);
