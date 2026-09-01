<?php

namespace App\Http;

use App\Authentication\CookieSupport;
use App\Models\Configuration\SearchEngineRegistry;
use App\Models\Configuration\SettingsSchema;

/**
 * The settings, arrived by query with no matching cookie, that a cookie-blind
 * visitor needs carried into every generated same-origin URL on this
 * request — the query-string counterpart to `CookieSupport::keyMissingCookie()`
 * for `key`, generalised from one hardcoded name to any setting matching
 * `SearchSettings::isValidSetting()`'s validation rules.
 *
 * Deliberately reimplements those rules rather than calling
 * `app(SearchSettings::class)->isValidSetting()`: `SearchSettings` is a
 * container singleton whose *first* resolution runs its full, request-reading
 * `boot()` (`SearchSettingsProvider`) and then never runs it again for the
 * rest of the request. This class is reached from `route()`/`url()`
 * (`CookieCarryingUrlGenerator`, `CookieSupport::carryIntoUrl()`), which fire
 * far earlier and far more often than a page's own settings logic expects —
 * including, concretely, Laravel's own test harness normalising a test URL
 * via `url()` *before* dispatching the request it belongs to. Resolving
 * `SearchSettings` there boots it against the wrong request and — being a
 * singleton — that wrong `$q`/`$fokus` then sticks for the request that
 * follows. `isValidSetting()`'s only real dependency is `available_foki`
 * (`array_keys($registry->foki)`, from the request-independent
 * `SearchEngineRegistry`), so deriving that directly avoids the whole
 * failure mode instead of working around one call site of it.
 *
 * `keyMissingCookie()` cannot be reused here: it requires a key already in
 * the query, which an anonymous cookie-blind visitor never has. Settings
 * need their own, per-name gate instead of one request-wide boolean — a
 * query parameter rides forward iff it names a recognised setting AND no
 * cookie of that same name is present. Self-perpetuating (once carried it
 * keeps riding, the same way `key` does) and self-correcting per name the
 * instant a real cookie for that name lands — no second one-hop marker is
 * needed the way `CookieSupport::MARKER` is for the "your browser is
 * blocking cookies" notice.
 *
 * Bound as a singleton in `AppServiceProvider::register()`: one instance per
 * request is load-bearing, not just convenient. `SettingsController`'s POST
 * handlers call `set()`/`forget()` as they queue or forget the matching
 * cookie, and a handler builds its redirect in two hops for one
 * response — `route('settings', …)` then `redirect($that)`, which runs
 * `to()`/`carryIntoUrl()` a second time. Both hops must agree on a name a
 * handler just removed, which only holds if both read the same mutable
 * instance rather than each re-deriving it from the request's original
 * query string.
 */
class SettingsCarry
{
    /** @var array<string,string>|null null = not yet booted for this request */
    private ?array $params = null;

    /** The settings currently being carried forward, as route()/url() parameters. */
    public function all(): array
    {
        $this->boot();
        return $this->params;
    }

    /** A handler just persisted this setting as a cookie; carry it forward too. */
    public function set(string $name, string $value): void
    {
        $this->boot();
        $this->params[$name] = $value;
    }

    /** A handler just forgot this setting's cookie; stop carrying it. */
    public function forget(string $name): void
    {
        $this->boot();
        unset($this->params[$name]);
    }

    private function boot(): void
    {
        if ($this->params !== null) {
            return;
        }
        $this->params = [];

        if (!app()->bound('request')) {
            return;
        }
        $request = app('request');
        $availableFoki = array_keys(get_object_vars(app(SearchEngineRegistry::class)->foki));

        foreach ($request->query() as $name => $value) {
            if (is_array($value)) {
                $value = $value[0] ?? '';
            }
            // `key` is one of SettingsSchema::globalSettingKeys(), so
            // isValidSetting() would otherwise accept it too — it has its
            // own, separate carrying mechanism (CookieSupport::keyMissingCookie()),
            // and MARKER is a one-hop signal, never a setting to persist.
            if ($name === 'key' || $name === CookieSupport::MARKER) {
                continue;
            }
            if ($request->cookie($name) !== null) {
                continue;
            }
            if (!self::isValidSetting($name, $value, $availableFoki)) {
                continue;
            }
            $this->params[$name] = $value;
        }
    }

    /**
     * `SearchSettings::isValidSetting()`'s validation rules, reimplemented
     * against a directly-derived `$availableFoki` rather than that stateful
     * singleton — see this class's docblock for why. Keep the two in sync.
     */
    private static function isValidSetting(string $name, string $value, array $availableFoki): bool
    {
        if (in_array($name, SettingsSchema::globalSettingKeys(), true)) {
            return true;
        }
        if (preg_match("/^([^_]+)_blpage$/", $name, $matches) && in_array($matches[1], $availableFoki, true)) {
            return true;
        }
        if (preg_match("/^([^_]+)_engine_(.*)$/", $name, $matches) && in_array($matches[1], $availableFoki, true) && in_array($value, ["on", "off"], true)) {
            return true;
        }
        if (preg_match("/^([^_]+)_setting_(.*)$/", $name, $matches) && in_array($matches[1], $availableFoki, true)) {
            return true;
        }
        return false;
    }
}
