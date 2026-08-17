<?php

namespace App\Support;

/**
 * The User-Agent MetaGer sends when it asks a search engine.
 *
 * It used to send the client's own header, verbatim. A comment in
 * Searchengine claimed a middleware had replaced it with "a almost completely
 * random useragent", but that middleware was aliased and never attached to a
 * route (removed separately), so what actually left the building was the
 * visitor's real User-Agent — the search term and the fingerprint together.
 *
 * Now every search leaves with one of two strings. Device class is kept
 * because engines that answer with HTML, rather than through an API, serve a
 * different page to a phone; everything else about the client is dropped.
 *
 * The version below is a plain string on purpose: it is one edit, and it wants
 * one. A User-Agent claiming a Firefox that is years old is as distinctive as
 * no anonymisation at all, so bump VERSION when it drifts far from current
 * Firefox. UpstreamUserAgentTest states this in a form that is hard to miss.
 */
class UpstreamUserAgent
{
    private const VERSION = "142.0";

    public const DESKTOP = "Mozilla/5.0 (X11; Linux x86_64; rv:" . self::VERSION . ") Gecko/20100101 Firefox/" . self::VERSION;

    public const MOBILE = "Mozilla/5.0 (Android 16; Mobile; rv:" . self::VERSION . ") Gecko/20100101 Firefox/" . self::VERSION;

    private ?string $value = null;

    /**
     * Memoised because one search builds one engine object per engine in the
     * fokus, and each of them asks. Bound as a singleton in AppServiceProvider,
     * so the User-Agent is parsed once per request rather than once per engine.
     */
    public function value(): string
    {
        return $this->value ??= self::for(Browser::fromRequest());
    }

    /**
     * Tablets count as mobile here, following DeviceDetector's own split. The
     * distinction that matters upstream is which layout an HTML engine serves,
     * and a tablet gets the mobile one.
     */
    public static function for(Browser $browser): string
    {
        return $browser->isMobile() ? self::MOBILE : self::DESKTOP;
    }
}
