<?php

namespace App\Authentication;

use App\Events\KeyChanged;
use Arr;
use Cache;
use Http;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Redis;
use Request;

class KeyUser implements Authenticatable
{

    public string $id;

    /**
     * The key associated with the user.
     *
     * @var string
     */
    public string $key;

    public bool $temporary = false;

    private KeyState|null $state = null;

    /**
     * The keyserver URL.
     *
     * @var string
     */
    private string $keyserver;

    /**
     * Create a new KeyUser instance.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->id = uniqid('key_user', true);
        $this->key = $key;

        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    public function getAuthIdentifierName(): string
    {
        return 'key';
    }

    public function getAuthIdentifier(): string
    {
        return $this->key;
    }

    public function getAuthPasswordName(): string
    {
        return 'key';
    }

    public function getAuthPassword(): string
    {
        return $this->key;
    }

    public function getRememberToken(): string
    {
        return ''; // KeyUser does not use remember tokens
    }

    public function setRememberToken($value): void
    {
        // KeyUser does not use remember tokens
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function getKeyState(): KeyState
    {
        if ($this->state === null) {
            if ($this->temporary) {
                $this->state = match (Request::header("tokenauthorization")) {
                    "empty" => KeyState::EMPTY ,
                    "low" => KeyState::LOW,
                    "full" => KeyState::FULL,
                    default => KeyState::NO_KEY, // Default to NO_KEY if no valid state is provided
                };
            } else {
                $key_data = $this->getKeyData();
                $current_charge = Arr::get($key_data, "charge", null);
                $this->state = match (true) {
                    $current_charge > 30 => KeyState::FULL,
                    $current_charge > 3 && $current_charge <= 30 => KeyState::LOW,
                    $current_charge <= 3 => KeyState::EMPTY ,
                    default => KeyState::NO_KEY,
                };

            }
        }
        return $this->state;
    }

    /**
     * Authorize the user for a specific token cost. The amount will be claimed on the key for
     * this process for the specified duration and is not available for other processes
     * during that time.
     *
     * @param float $token_cost
     * @param int $claim_duration_seconds
     * @return bool
     */
    public function authorize(float $token_cost, $claim_duration_seconds = 30): bool
    {
        $claims = Redis::connection(config('cache.stores.redis.connection'))->hgetall("keyserver:claims:" . $this->key);

        $key_data = $this->getKeyData();
        $current_charge = Arr::get($key_data, "charge", 0);

        foreach ($claims as $id => $amount) {
            if ($id !== $this->id)
                $current_charge -= max($amount, 0); // Ensure we don't subtract negative amounts
        }
        $current_charge -= $token_cost;

        Redis::connection(config('cache.stores.redis.connection'))->hincrbyfloat("keyserver:claims:" . $this->key, $this->id, $token_cost);
        Redis::connection(config('cache.stores.redis.connection'))->hexpireat("keyserver:claims:" . $this->key, now()->addSeconds($claim_duration_seconds)->timestamp, [$this->id]);

        return $current_charge >= 0;
    }

    public function makePayment(float $token_cost): bool
    {
        $claim_amount = Redis::connection(config('cache.stores.redis.connection'))->hget("keyserver:claims:" . $this->key, $this->id);
        if ($claim_amount === null)
            $claim_amount = 0;

        if ($claim_amount > 0 && $claim_amount < $token_cost) {
            if ($this->authorize($token_cost - $claim_amount, 30)) {
                // If we have a claim that is less than the token cost, we cannot proceed
                $claim_amount = $token_cost;
            } else {
                return false;
            }
        }

        $token_cost = max($token_cost, 0); // Ensure we don't process negative costs
        if (abs($token_cost - 0) < PHP_FLOAT_EPSILON)
            return true;
        $key_response = Http::withHeaders([
            "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
            "Content-Type" => "application/json",
        ])->post($this->keyserver . "/key/" . urlencode($this->key) . "/discharge", [
                    "amount" => $token_cost,
                ]);

        if ($key_response->successful()) {
            $key_response = $key_response->json();
            $current_charge = Arr::get($key_response, "charge");
            if ($current_charge === null) {
                return false;
            }
            Cache::put("keyserver:key:" . $this->key, $key_response, now()->addMinutes(30)); // Cache for 30 minutes
            Redis::connection(config('cache.stores.redis.connection'))->hincrbyfloat("keyserver:claims:" . $this->key, $this->id, -$token_cost);
            return true;
        }

        return false;
    }

    private function getKeyData(): array|null
    {
        if (!$key_response = Cache::get("keyserver:key:" . $this->key)) {
            // Fetch key data from the keyserver
            $key_response = Http::withHeaders([
                "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")
            ])->get($this->keyserver . "/key/" . urlencode($this->key));

            if ($key_response->successful()) {
                $key_response = $key_response->json();
                $current_charge = Arr::get($key_response, "charge");
                if ($current_charge === null) {
                    return null;
                }
                Cache::put("keyserver:key:" . $this->key, $key_response, now()->addMinutes(30)); // Cache for 30 minutes
                KeyChanged::dispatch($this->key, 0, $current_charge);
                return $key_response;
            } else {
                return null;
            }
        } else {
            return $key_response;
        }
    }
}
