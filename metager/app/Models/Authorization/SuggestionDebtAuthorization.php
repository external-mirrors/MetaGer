<?php

namespace App\Models\Authorization;

use App;
use App\Localization;
use App\SearchSettings;
use App\Support\Browser;
use App\Support\RedisFailover;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use LaravelLocalization;
use Predis\PredisException;
use RateLimiter;
use Request;

/**
 * We cannot make payments for suggestions executed through the Opensearchdescription of the browser
 * since the Firefox Browser does not provide website data for those requests and our extension cannot
 * intercept or modify requests to enable anonymous Tokens.
 *
 * We will grant anonymous suggestion requests to a user which will be paid on later search requests on
 * a credit base.
 *
 * ## Every call here is on the search path, before the search
 *
 * AuthenticationValidation reads the debt before it lets a request through to
 * MetaGerSearch, and writes the credit back immediately after. So these run on
 * every authenticated search, ahead of anything the user asked for — and until
 * this class was guarded, a Sentinel promotion landing on any of them was a 503
 * for a search that was otherwise perfectly serviceable. Most of them are
 * writes (`hincrbyfloat`, `hset`, `hexpireat`), which is exactly what a demoted
 * node answers `-READONLY`.
 *
 * Hence {@see RUN}: every operation is retried across a failover and then, if
 * it still cannot be done, given up on. That is the right trade here and it is
 * not a close call. The stake is a tenth of a token of suggestion credit,
 * bounded by MAX_CREDIT and expiring in two days by itself; the alternative is
 * an error page. Nobody should lose a search because we could not remember that
 * they had been offered a suggestion.
 */
class SuggestionDebtAuthorization extends Authorization
{
    private const CACHE_PREFIX = "suggestion:authorization:";
    private const MAX_CREDIT = 1.0;

    /**
     * The connection these live on — the cache one, not the default.
     *
     * Named in one place because it is also what {@see RUN} has to hand
     * RedisFailover: the retry drops the pooled connection before trying again,
     * and dropping the wrong one would leave the retry pinned to the same
     * demoted node it just failed against.
     */
    private static function CONNECTION(): string
    {
        return config("cache.stores.redis.connection");
    }

    private static function REDIS(): mixed
    {
        return Redis::connection(self::CONNECTION());
    }

    /**
     * Do it, retrying across a failover; if that fails too, carry on without.
     *
     * @template T
     * @param callable():T $operation
     * @param T $default what to answer with when it could not be done
     * @return T
     */
    private static function RUN(callable $operation, mixed $default = null): mixed
    {
        try {
            return RedisFailover::retry($operation, connection: self::CONNECTION());
        } catch (PredisException $e) {
            Log::warning("Suggestion debt bookkeeping skipped: " . $e->getMessage());

            return $default;
        }
    }

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
        if (!app(SearchSettings::class)->suggestion_addressbar)
            return;
        $expiration = now()->addDays(2);

        $cache_key = self::GET_CACHE_KEY();

        // One RUN per command rather than one around the block. `hincrbyfloat`
        // is not idempotent, and retrying a block whose *last* command failed
        // would apply the increment a second time. Per command, a retry can
        // only ever repeat the one command whose reply was lost, which is the
        // same bounded risk KeyUser::authorize() already accepts for the claim
        // it writes.
        $current_value = floatval(self::RUN(
            fn() => self::REDIS()->hincrbyfloat($cache_key, "credit", $amount),
            0
        ));
        if ($current_value > self::MAX_CREDIT) {
            self::RUN(fn() => self::REDIS()->hset($cache_key, "credit", self::MAX_CREDIT));
        } else if ($current_value < 0) {
            self::RUN(fn() => self::REDIS()->hset($cache_key, 0, "credit"));
        }
        self::RUN(fn() => self::REDIS()->hexpireat($cache_key, $expiration->getTimestamp(), ["credit"]));
    }

    public static function GET_CREDIT(): float
    {
        $cache_key = self::GET_CACHE_KEY();
        $current_value = self::RUN(fn() => self::REDIS()->hget($cache_key, "credit"));
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
        $current_value = floatval(self::RUN(
            fn() => self::REDIS()->hincrbyfloat($cache_key, "debt", $amount),
            0
        ));
        if ($current_value < 0) {
            self::RUN(fn() => self::REDIS()->hset($cache_key, 0, "debt"));
        }
        self::RUN(fn() => self::REDIS()->hexpireat($cache_key, $expiration->getTimestamp(), ["debt"]));
    }

    public static function GET_DEBT(): float
    {
        $cache_key = self::GET_CACHE_KEY();
        $current_value = self::RUN(fn() => self::REDIS()->hget($cache_key, "debt"));
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

        $stored_settings = self::RUN(fn() => self::REDIS()->hget($cache_key, "settings"));
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
            self::RUN(fn() => self::REDIS()->hset($cache_key, "settings", json_encode($stored_settings)));
            self::RUN(fn() => self::REDIS()->hexpireat($cache_key, $expiration->getTimestamp(), ["settings"]));
        }
    }

    public static function LOAD_SETTINGS()
    {
        $settings = app(SearchSettings::class);
        $stored_settings = self::RUN(fn() => self::REDIS()->hget(self::GET_CACHE_KEY(), "settings"));
        if ($stored_settings !== null) {
            $stored_settings = json_decode($stored_settings, true);
            $settings->suggestion_provider = $stored_settings["provider"];
            $settings->suggestion_delay = $stored_settings["delay"];
            $settings->suggestion_addressbar = $stored_settings["addressbar"];
            if (!$stored_settings["addressbar"])
                $settings->suggestion_provider = "off";

            $agent = Browser::fromRequest();
            if ($agent->geckoVersion() > 0) {
                $settings->suggestion_delay = 200;
            }

            App::setLocale($stored_settings["locale"]);
            LaravelLocalization::setLocale($stored_settings["locale"]);
        }
    }

    public static function REMOVE_SETTINGS()
    {
        self::RUN(fn() => self::REDIS()->hdel(self::GET_CACHE_KEY(), ["settings"]));
    }

    public static function GET_CACHE_KEY(): string
    {
        $cache_key = self::CACHE_PREFIX;
        $cache_key .= sha1(Request::ip() . Request::userAgent() . implode(",", Request::getLanguages()));
        return $cache_key;
    }
}