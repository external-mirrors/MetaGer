<?php

namespace App\Search;

/**
 * One sitelink as the page shows it: its title, where it leads, and the same
 * place in SafeBrowse — the fields parts/result-proxy-link reads.
 *
 * Not Models\DeepResults\Button with a field added: Buttons are cached with
 * their result (the loader entry), and the SafeBrowse link has no business
 * there either. See Sitelinks.
 */
final class Sitelink
{
    public function __construct(
        public readonly string $title,
        public readonly string $link,
        public readonly string $proxyLink,
    ) {
    }
}
