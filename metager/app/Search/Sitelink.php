<?php

namespace App\Search;

/**
 * One sitelink as the page shows it: its title, where it leads, and the same
 * place through the proxy — the two fields parts/result-proxy-link reads.
 *
 * Not Models\DeepResults\Button with a field added: Buttons are cached with
 * their result (the loader entry), and the page's signed URL has no business
 * there. See Sitelinks.
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
