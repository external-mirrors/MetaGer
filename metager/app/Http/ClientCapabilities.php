<?php

namespace App\Http;

use Illuminate\Http\Request;

/**
 * Version gates for the clients that render MetaGer inside their own shell (app, webextension).
 *
 * The server already branches on `mg-app` for token payments (see AuthenticationValidation);
 * this is the same header used for the same purpose, kept here so the SafeBrowse gate has one
 * definition instead of being restated in a Blade condition and in a cache validator.
 */
class ClientCapabilities
{
    /**
     * First app generation whose key handling SafeBrowse can actually authenticate.
     *
     * The 5.x Android app never puts a key where SafeBrowse can read it: it keeps the key in
     * SharedPreferences, actively deletes the `key` cookie out of the WebView after reading it
     * (TokenManager::consumeBrowserdata), and sends a `key` header only on the key-management
     * paths — never `anonymous-token-key`. Everything else is paid for out of band with
     * `tokenauthorization` / `anonymous-token-payment-id`, which are attached by
     * WebView.loadUrl() to the *document* request only and therefore never reach a WebSocket
     * upgrade. So a 5.x user reaching SafeBrowse can only ever be told `no_key`.
     *
     * 6.x uses `Anonymous-Token-Key`, which AuthWrapper reads off the upgrade, and works.
     */
    private const MIN_APP_VERSION_SAFEBROWSE = '6.0';

    /**
     * Whether this client can complete a SafeBrowse session, i.e. whether the result page should
     * offer the SafeBrowse upgrade at all. Clients that cannot keep the plain old-proxy link.
     *
     * A missing `mg-app` header means "not the app" (browser, webextension), which is fine — only
     * a header that is present *and* below the cutoff disables the upgrade.
     */
    public static function supportsSafebrowse(Request $request): bool
    {
        $appVersion = $request->header('mg-app');
        if ($appVersion === null || $appVersion === '') {
            return true;
        }
        return version_compare($appVersion, self::MIN_APP_VERSION_SAFEBROWSE, '>=');
    }
}
