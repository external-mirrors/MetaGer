<?php

namespace App\Search\Fetch;

/**
 * Turns one fetch mission into the curl options that carry it out.
 *
 * A mission is what FPM pushes onto the fetch queue for a single search engine
 * (see Searchengine::startSearch) and what the requests:fetcher worker pops off
 * it: a url, the hash to store the answer under, the upstream user agent, and
 * whatever headers, credentials and curl overrides that engine needs.
 *
 * Split out of RequestFetcher so the options can be asserted on without a
 * network, a worker loop or a Redis queue. Everything here is a pure function
 * of the mission and the fetcher configuration; the command keeps the curl
 * handle, the multi handle and the loop.
 *
 * ## Order matters
 *
 * The options are assigned one at a time rather than merged. curl option
 * constants are integers, and array_merge renumbers integer keys — merging two
 * option arrays would quietly produce a third one that means nothing. The order
 * below is the order the command applied them in, and it is load-bearing:
 * `curlopts` from the mission overrides the defaults, and the proxy, credential
 * and header blocks come after it.
 */
class MissionOptions
{
    /**
     * @param array<string, mixed> $mission the decoded fetch queue entry
     * @return array<int, mixed> curl option constant => value
     */
    public static function for(array $mission): array
    {
        $options = [
            CURLOPT_URL => $mission["url"],
            CURLOPT_PRIVATE => self::privateTag($mission),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => $mission["useragent"],
            CURLOPT_FOLLOWLOCATION => true,
            // "" advertises every encoding this libcurl was built with and
            // decodes the answer before we ever see it, so the parsers get the
            // same bytes they always did. Until this line, MetaGer asked for
            // none and every engine answered uncompressed.
            //
            // Set it here and not as a request header: an Accept-Encoding curl
            // is not aware of is sent as-is and the body comes back encoded,
            // which reaches the parser as binary and looks like an engine
            // returning nonsense.
            CURLOPT_ACCEPT_ENCODING => "",
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_MAXCONNECTS => 500,
            CURLOPT_LOW_SPEED_LIMIT => 50000,
            CURLOPT_LOW_SPEED_TIME => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_TCP_KEEPALIVE => 1,
            CURLOPT_TCP_KEEPIDLE => 600,
            CURLOPT_TCP_KEEPINTVL => 15,
        ];

        // Per-engine overrides — today this is how a POST body is set.
        foreach ($mission["curlopts"] ?? [] as $option => $value) {
            $options[(int) $option] = $value;
        }

        if (self::usesProxy($mission)) {
            $options[CURLOPT_PROXY] = config("metager.metager.fetcher.proxy.host");
            $user = config("metager.metager.fetcher.proxy.user");
            $password = config("metager.metager.fetcher.proxy.password");
            if (!empty($user) && !empty($password)) {
                $options[CURLOPT_PROXYUSERPWD] = $user . ":" . $password;
            }
            $options[CURLOPT_PROXYPORT] = config("metager.metager.fetcher.proxy.port");
            $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
        }

        if (!empty($mission["username"]) && !empty($mission["password"])) {
            $options[CURLOPT_USERPWD] = $mission["username"] . ":" . $mission["password"];
        }

        if (!empty($mission["headers"])) {
            $headers = [];
            foreach ($mission["headers"] as $key => $value) {
                $headers[] = $key . ": " . $value;
            }
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        return $options;
    }

    /**
     * What the worker reads back off the finished handle to know what it just
     * fetched. Three fields in one string because CURLOPT_PRIVATE holds one.
     */
    public static function privateTag(array $mission): string
    {
        return $mission["resulthash"] . ";" . $mission["cacheDuration"] . ";" . $mission["name"];
    }

    /**
     * Missions go through the proxy unless they opt out, and only if one is
     * configured. The opt-out is per engine: `"proxy": false` in the mission.
     */
    private static function usesProxy(array $mission): bool
    {
        if (array_key_exists("proxy", $mission) && $mission["proxy"] !== true) {
            return false;
        }

        return !empty(config("metager.metager.fetcher.proxy.host"))
            && !empty(config("metager.metager.fetcher.proxy.port"));
    }
}
