<?php

namespace App\Http;

use App\Authentication\CookieSupport;
use App\SearchSettings;

/**
 * The settings, arrived by query with no matching cookie, that a cookie-blind
 * visitor needs carried into every generated same-origin URL on this
 * request — the query-string counterpart to `CookieSupport::keyMissingCookie()`
 * for `key`, generalised from one hardcoded name to any setting
 * `SearchSettings::isValidSetting()` recognises.
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
        $settings = app(SearchSettings::class);

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
            if (!$settings->isValidSetting($name, $value)) {
                continue;
            }
            $this->params[$name] = $value;
        }
    }
}
