<?php

namespace App;

use \App\Models\Result;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContentWarnings
{
    // If a whole search engine needs to receive content warnings
    // enter its Parser Class Name here
    const CONTENT_WARNING_ENGINES = [
        "Yandex",
    ];

    const CACHE_KEY = "content_warnings";
    const CACHE_TTL = 300; // 5 Minutes Cache

    /**
     * Enables Content Warnings for given result if needed
     * 
     * @param Result &$result
     * 
     * @return boolean
     */
    public static function enableContentWarnings(Result &$result)
    {
        if (!self::checkResult($result)) {
            // No content warning necessary
            return;
        }

        $target_url = $result->link;

        $new_target_url = route('content-warning', [
            "url" => $target_url,
            "result-page" => url()->full(),
            "pw" => "test",
        ]);

        $result->link = $new_target_url;
        $result->content_warning = true;
    }

    /**
     * Checks the Result if it needs a content warning. It depends on a public list of 
     * possibly compromised media websites and the source search engine.
     * 
     * @param Result &$result
     * 
     * @return boolean
     */
    private static function checkResult(Result &$result)
    {
        foreach (self::CONTENT_WARNING_ENGINES as $engine) {
            if ($result->provider->{"parser-class"} === $engine) {
                return true;
            }
        }


        $targetUrl = $result->originalLink;

        // Extract Domain from URL
        $targetDomain = parse_url($targetUrl, PHP_URL_HOST);

        $domain_list = self::getContentWarningList();
        foreach ($domain_list as $domain_entry) {
            $domain_entry_regexp = preg_quote($domain_entry, "/");
            // We will allow *. notation at the start of the file
            // Both characters would have been escaped by above function
            if (strpos($domain_entry_regexp, '\*\.') === 0) {
                $domain_entry_regexp = substr_replace($domain_entry_regexp, '([^\.]+\.)*?', 0, 4);
            }
            $domain_entry_regexp = "/^" . $domain_entry_regexp . '$/';
            if (preg_match($domain_entry_regexp, $targetDomain)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieves a list of domains to use content warnings on
     * To Reduce Disk I/O the list of possibly dangerous domains gets cached in redis.
     * This function reads in the file from disk and loads it into the cache.
     * An expiring redis key is used to determine if the list is loaded or not.
     * 
     * @return array A list of Domains to implement content warnings on. Includes full domains and *.domain.com
     */
    private static function getContentWarningList()
    {
        $domain_list = Cache::get(self::CACHE_KEY, null);
        if ($domain_list !== null) {
            return $domain_list;
        }

        // Domain List is not in cache yet: Load it from disk
        $content_warning_list_file_path = storage_path("app/public/content_warning_domains.txt");

        $domain_list = [];

        $fh = fopen($content_warning_list_file_path, "r");

        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if (strpos($line, "//") === 0) {
                // This is a comment. Skip the line
                continue;
            }
            $domain_to_validate = $line;
            // Checks if the Domain starts with .* to include all subdomains
            if (preg_match("/^\*\.(.*)/", $domain_to_validate, $matches)) {
                $domain_to_validate = $matches[1];
            }

            if ($domain_to_validate === false) {
                Log::error("[Content Warnings]: " . date("Y-m-d H:i:s") . " - $line is not a valid domain.");
                continue;
            } else {
                $domain_list[] = $line;
            }
        }

        fclose($fh);

        Cache::put(self::CACHE_KEY, $domain_list, self::CACHE_TTL);

        return $domain_list;
    }
}
