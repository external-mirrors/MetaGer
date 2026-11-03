<?php

namespace App\Search;

/**
 * The one place that says what a blocklist value and a blocklist term look like.
 *
 * Applied on both sides of every comparison: to what an operator (or suma-crm)
 * stores, and to the host, link and query of each result at lookup time. That
 * is what lets an entry typed as `https://www.Example.org/a/` block a result
 * whose link arrived as `http://example.org/a`.
 *
 * Hosts and links are cut the way Result::getUrlElements cuts them — a scheme
 * is assumed when missing, `www.` is dropped, port, query and fragment go — and
 * then made comparable: the host is lowercased (hosts are case-insensitive; the
 * path is not, so it keeps its case) and a trailing slash is dropped.
 * Tests\Unit\BlocklistNormalizerTest pins the agreement with Result.
 */
class BlocklistNormalizer
{
    /** `https://www.Example.org:8080/a?b=c` → `example.org` */
    public static function host(string $url): string
    {
        return self::parts($url)[0];
    }

    /** `https://www.Example.org/a/b/?c=d` → `example.org/a/b` */
    public static function link(string $url): string
    {
        [$host, $path] = self::parts($url);

        return $host === "" ? "" : $host . rtrim($path, "/");
    }

    /**
     * An already-stripped host or link (what Result holds) made comparable.
     * Cheaper than host()/link(), which have to parse first: this runs once
     * per result.
     */
    public static function strippedHost(string $strippedHost): string
    {
        return strtolower($strippedHost);
    }

    public static function strippedLink(string $strippedLink): string
    {
        $slash = strpos($strippedLink, "/");
        if ($slash === false) {
            return strtolower($strippedLink);
        }

        return strtolower(substr($strippedLink, 0, $slash)) . rtrim(substr($strippedLink, $slash), "/");
    }

    /**
     * Words, comparable across case, umlauts and other diacritics.
     *
     * Lowercased; ä ö ü ß written out the German way (so `Müller` and
     * `Mueller` are one name); every other diacritic dropped (`é` → `e`);
     * split on anything that is not a letter or a digit; de-duplicated and
     * sorted, so a set of terms has exactly one spelling.
     *
     * @param string|array<int, string> $text
     * @return list<string>
     */
    public static function terms(string|array $text): array
    {
        if (is_array($text)) {
            $text = implode(" ", $text);
        }

        $text = mb_strtolower($text, "UTF-8");
        $text = strtr($text, ["ä" => "ae", "ö" => "oe", "ü" => "ue", "ß" => "ss"]);

        $decomposed = \Normalizer::normalize($text, \Normalizer::FORM_D);
        if (is_string($decomposed)) {
            $text = preg_replace('/\p{Mn}+/u', "", $decomposed) ?? $text;
        }

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_values(array_unique($tokens));
        sort($tokens, SORT_STRING);

        return $tokens;
    }

    /**
     * @return array{0: string, 1: string} lowercased host without `www.`, path
     */
    private static function parts(string $url): array
    {
        $url = trim($url);
        if (stripos($url, "http") !== 0) {
            $url = "http://" . $url;
        }

        $parts = parse_url($url) ?: [];
        $host = strtolower($parts["host"] ?? "");
        if (str_starts_with($host, "www.")) {
            $host = substr($host, 4);
        }
        // parse_url() takes "not a url" for a host; no result ever has one.
        if (preg_match('/\s/u', $host)) {
            return ["", ""];
        }

        return [$host, $parts["path"] ?? ""];
    }
}
