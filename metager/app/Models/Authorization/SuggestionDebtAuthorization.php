<?php

namespace App\Models\Authorization;

use App;
use App\Localization;
use App\SearchSettings;
use foroco\BrowserDetection;
use Illuminate\Support\Facades\Redis;
use LaravelLocalization;
use RateLimiter;
use Request;

/**
 * We cannot make payments for suggestions executed through the Opensearchdescription of the browser
 * since the Firefox Browser does not provide website data for those requests and our extension cannot
 * intercept or modify requests to enable anonymous Tokens.
 * 
 * We will grant anonymous suggestion requests to a user which will be paid on later search requests on
 * a credit base.
 */
class SuggestionDebtAuthorization extends Authorization
{
    private const CACHE_PREFIX = "suggestion:authorization:";
    private const MAX_CREDIT = 1.0;

    public function __construct()
    {
        $this->availableTokens = round(max(self::GET_CREDIT() - self::GET_DEBT(), 0), 1);
    }

    public function getToken(): null
    {
        return null;
    }

    public function makePayment(float $cost): bool
    {
        if ($cost === 0)
            return true;
        if (!$this->canDoAuthenticatedSearch(true)) {
            return false;
        }
        self::ADD_DEBT($cost);
        return true;
    }

    /**
     * 
     * This function adds a temporary credit to a requesting client which is increased when a regular search is executed
     * 
     * @param float $amount
     * @return void
     */
    public static function ADD_CREDIT(float $amount = 0.1)
    {
        $expiration = now()->addDays(2);

        $cache_key = self::GET_CACHE_KEY();
        $current_value = Redis::connection(config('cache.stores.redis.connection'))->hincrbyfloat($cache_key, "credit", $amount);
        $current_value = floatval($current_value);
        if ($current_value > self::MAX_CREDIT) {
            Redis::connection(config('cache.stores.redis.connection'))->hset($cache_key, "credit", self::MAX_CREDIT);
        } else if ($current_value < 0) {
            Redis::connection(config('cache.stores.redis.connection'))->hset($cache_key, 0, "credit");
        }
        Redis::connection(config('cache.stores.redis.connection'))->hexpireat($cache_key, $expiration->getTimestamp(), ["credit"]);
    }

    public static function GET_CREDIT(): float
    {
        $cache_key = self::GET_CACHE_KEY();
        $current_value = Redis::connection(config('cache.stores.redis.connection'))->hget($cache_key, "credit");
        if ($current_value === null) {
            $current_value = 0;
        } else {
            $current_value = floatval($current_value);
        }
        return round($current_value, 1);
    }

    /**
     * 
     * This function adds a temporary credit to a requesting client which is increased when a regular search is executed
     * 
     * @param float $amount
     * @return void
     */
    public static function ADD_DEBT(float $amount = 0.1)
    {
        $expiration = now()->addDays(2);

        $cache_key = self::GET_CACHE_KEY();
        $current_value = Redis::connection(config('cache.stores.redis.connection'))->hincrbyfloat($cache_key, "debt", $amount);
        $current_value = floatval($current_value);
        if ($current_value < 0) {
            Redis::connection(config('cache.stores.redis.connection'))->hset($cache_key, 0, "debt");
        }
        Redis::connection(config('cache.stores.redis.connection'))->hexpireat($cache_key, $expiration->getTimestamp(), ["debt"]);
    }

    public static function GET_DEBT(): float
    {
        $cache_key = self::GET_CACHE_KEY();
        $current_value = Redis::connection(config('cache.stores.redis.connection'))->hget($cache_key, "debt");
        if ($current_value === null) {
            $current_value = 0;
        } else {
            $current_value = floatval($current_value);
        }
        return round($current_value, 1);
    }

    /**
     * Stores current suggestion settings in a cache so the latest stored value will be used on
     * unauthorized requests.
     * If any user disabled suggestions they will only be enabled when a user switches his setting from off to on
     * If nothing is currently stored and the current user has suggestions disabled nothing will be stored because the default setting (off) applies automatically
     * @param bool $reenable
     * @return void
     */
    public static function UPDATE_SETTINGS(bool $reenable = false)
    {
        $cache_key = self::GET_CACHE_KEY();
        $expiration = now()->addDays(2);

        $settings = app(SearchSettings::class);

        $agent = (new BrowserDetection())->getAll(\Request::userAgent());
        if ($agent["browser_gecko_version"] > 0) {
            $settings->suggestion_delay = SearchSettings::SUGGESTION_DELAY_SHORT;
        }

        $stored_settings = Redis::connection(config('cache.stores.redis.connection'))->hget($cache_key, "settings");
        if ($stored_settings !== null) {
            $stored_settings = json_decode($stored_settings, true);
            if (in_array($settings->suggestion_provider, [null, "off"])) {
                // CUrrent User wants suggestions to be disabled => Always Disable 
                $stored_settings["provider"] = $settings->suggestion_provider;
            } else {
                if (in_array($stored_settings["provider"], [null, "off"])) {
                    // User wants to reenable suggestions with possibly another user having it disabled => Only reenable if variable is true
                    // Variable will only be true when set from settings change
                    if ($reenable) {
                        $stored_settings["provider"] = $settings->suggestion_provider;
                    } else {
                        // Do not reenable suggestions
                        $stored_settings = null;
                    }
                }
            }

        } else if ($settings->suggestion_addressbar && !in_array($settings->suggestion_provider, [null, "off"])) {
            $stored_settings = ["provider" => $settings->suggestion_provider];
        }
        if ($stored_settings !== null) {
            $stored_settings["locale"] = Localization::getLanguage() . "-" . Localization::getRegion();
            $stored_settings["delay"] = $settings->suggestion_delay;
            $stored_settings["addressbar"] = $settings->suggestion_addressbar;
            Redis::connection(config('cache.stores.redis.connection'))->hset($cache_key, "settings", json_encode($stored_settings));
            Redis::connection(config('cache.stores.redis.connection'))->hexpireat($cache_key, $expiration->getTimestamp(), ["settings"]);
        }
    }

    public static function LOAD_SETTINGS()
    {
        $settings = app(SearchSettings::class);
        $stored_settings = Redis::connection(config('cache.stores.redis.connection'))->hget(self::GET_CACHE_KEY(), "settings");
        if ($stored_settings !== null) {
            $stored_settings = json_decode($stored_settings, true);
            $settings->suggestion_provider = $stored_settings["provider"];
            $settings->suggestion_delay = $stored_settings["delay"];
            if (!$stored_settings["addressbar"])
                $settings->suggestion_provider = "off";
            App::setLocale($stored_settings["locale"]);
            LaravelLocalization::setLocale($stored_settings["locale"]);
        }
    }

    public static function REMOVE_SETTINGS()
    {
        Redis::connection(config('cache.stores.redis.connection'))->hdel(self::GET_CACHE_KEY(), ["settings"]);
    }

    public static function GET_CACHE_KEY(): string
    {
        $cache_key = self::CACHE_PREFIX;
        $cache_key .= Request::ip() . ":" . sha1(Request::userAgent() . implode(",", Request::getLanguages()));
        return $cache_key;
    }
}