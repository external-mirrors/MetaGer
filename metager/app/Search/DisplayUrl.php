<?php

namespace App\Search;

/**
 * The address drawn under a result's title: "host › segment › segment".
 *
 * Built from the result's link, for every engine the same way. Engines used to
 * send their own — Brave "host › path", Staan "host > path", everyone else the
 * bare URL — so one page mixed three styles, and the long ones were full URLs
 * with query strings.
 *
 * Only what helps recognise the page is kept: the host without "www.", and the
 * path, decoded, its middle elided past three segments and each segment cut at
 * a length. The query and fragment are dropped. The full link stays on the
 * element's title.
 */
final class DisplayUrl
{
    private const SEPARATOR = " › ";
    private const ELLIPSIS = "…";
    private const MAX_SEGMENTS = 3;
    private const MAX_SEGMENT_LENGTH = 32;

    /** Null when the link has no host to show. */
    public static function fromLink(string $link): ?string
    {
        $parts = parse_url(trim($link));
        if ($parts === false || empty($parts["host"])) {
            return null;
        }

        // A punycode host stays punycode, the way browsers keep hosts that
        // mix scripts: decoded, a Cyrillic "аpple.com" would read as Apple's.
        $host = strtolower($parts["host"]);
        if (str_starts_with($host, "www.")) {
            $host = substr($host, 4);
        }
        if (isset($parts["port"])) {
            $host .= ":" . $parts["port"];
        }

        $segments = array_values(array_filter(
            array_map(self::segment(...), explode("/", $parts["path"] ?? "")),
            fn(string $segment) => $segment !== ""
        ));
        if (count($segments) > self::MAX_SEGMENTS) {
            $segments = [$segments[0], self::ELLIPSIS, $segments[count($segments) - 1]];
        }

        return implode(self::SEPARATOR, [$host, ...$segments]);
    }

    private static function segment(string $raw): string
    {
        $decoded = rawurldecode($raw);
        if (!mb_check_encoding($decoded, "UTF-8")) {
            $decoded = $raw;
        }
        // After decoding, so an encoded right-to-left override cannot reorder
        // what is drawn ("bild\u{202E}gpj.exe" showing as "bildexe.jpg").
        $decoded = preg_replace('/\p{C}+/u', "", $decoded);

        if (mb_strlen($decoded) > self::MAX_SEGMENT_LENGTH) {
            $decoded = mb_substr($decoded, 0, self::MAX_SEGMENT_LENGTH - 1) . self::ELLIPSIS;
        }

        return $decoded;
    }
}
