<?php

namespace App\Search;

/**
 * The operator blacklists: which results MetaGer will not show, and whose
 * descriptions it will not print.
 *
 * Three plain text files in config/, one entry per line:
 *
 *   blacklistDomains.txt         a host — every result on it is dropped
 *   blacklistUrl.txt             a host+path — that one page is dropped; a
 *                                `page|query` line drops it for one query only
 *   blacklistDescriptionUrl.txt  a host+path — the result stays, its
 *                                description is blanked
 *
 * None of them is in the repository. In production each is a subPath mount of a
 * key in the `secrets` Secret (chart/templates/_helpers.tpl), so a checkout has
 * none and the filter is simply inert.
 *
 * This used to live in MetaGer::__construct, which read and exploded all three
 * files on every request, and in Result::isBlackListed, which then ran in_array
 * over the resulting flat arrays three times per result. Both costs were linear
 * in the length of the files: at ten thousand lines the read alone was ~3.4 ms
 * per request and the scans ~0.5 ms per twenty results, and both grew from
 * there. Here the file is read once per worker process and the entries are held
 * as a hash map, so a lookup costs the same whatever the file's length.
 *
 * ## Two pieces of behaviour that are quirks, not decisions
 *
 * The domain list is trimmed line by line and the url list is not, so an
 * indented url entry matches nothing — and neither does any entry in a file
 * saved with CRLF line endings. And the two lists are behind one `&&`: unless
 * *both* files exist, neither loads, so an operator who ships only a domain
 * blacklist gets no filtering at all. Both are kept as they were and pinned in
 * tests/Feature/Search/BlacklistFilterTest, which names each of them as
 * something a later change may fix on purpose.
 *
 * What did change is the comparison. in_array() defaults to loose comparison,
 * which for two numeric strings compares them as numbers — so a line reading
 * `1.20` blocked the host `1.2`. Hash lookups are exact.
 */
class Blacklists
{
    private const DOMAINS = "blacklistDomains.txt";
    private const URLS = "blacklistUrl.txt";
    private const DESCRIPTIONS = "blacklistDescriptionUrl.txt";

    /**
     * Parsed files, kept for the lifetime of the process rather than the
     * request. Keyed by path and by the file's mtime and size, so an edit is
     * picked up without a restart — which matters on a developer machine, and
     * not in production, where a subPath mount of a Secret key does not change
     * while the pod lives.
     *
     * Static, so tests must reset it: two tests writing different content of
     * the same length to the same path within one second are indistinguishable
     * to a stat(). Tests\TestCase::setUp calls flush().
     *
     * @var array<string, array{stamp: string, entries: array<string, true>}>
     */
    private static array $files = [];

    /**
     * The three lists as this instance resolved them, so the stat() that checks
     * the memo happens once per instance and not once per result. The instance
     * is a container singleton, so that means once per request — which is also
     * the right granularity: a file edited halfway through a request should not
     * apply to half of its results.
     *
     * @var array<string, array<string, true>>
     */
    private array $resolved = [];

    public function __construct(private ?string $directory = null) {}

    /** Forget every parsed file. For tests; see the note on self::$files. */
    public static function flush(): void
    {
        self::$files = [];
    }

    /**
     * Is this result blacklisted?
     *
     * The host has to be readable for any of it to apply: a link the url parser
     * could not take a host out of is never blacklisted, not even by an exact
     * url entry. That is how the check has always read — one guard in front of
     * all three lookups — and it is what stops an empty line in the domain file
     * from blocking every result whose host failed to parse.
     *
     * @param string $strippedHost host with scheme, `www.`, port and query removed
     * @param string $strippedLink the same, plus the path
     * @param string $query the search term, matched case-insensitively
     */
    public function blocksResult(string $strippedHost, string $strippedLink, string $query): bool
    {
        if ($strippedHost === "") {
            return false;
        }

        $domains = $this->domains();
        if (isset($domains[$strippedHost])) {
            return true;
        }

        $urls = $this->urls();

        return isset($urls[$strippedLink])
            || isset($urls[$strippedLink . "|" . strtolower($query)]);
    }

    /**
     * Should this result be shown without its description?
     *
     * Unlike the other two lists, this one loads on its own.
     */
    public function blocksDescription(string $strippedLink): bool
    {
        return isset($this->descriptions()[$strippedLink]);
    }

    /** @return array<string, true> */
    private function domains(): array
    {
        return $this->resolved[self::DOMAINS] ??= $this->bothListsPresent()
            ? $this->entries(self::DOMAINS, trim: true)
            : [];
    }

    /** @return array<string, true> */
    private function urls(): array
    {
        return $this->resolved[self::URLS] ??= $this->bothListsPresent()
            ? $this->entries(self::URLS, trim: false, dropComments: true)
            : [];
    }

    /** @return array<string, true> */
    private function descriptions(): array
    {
        return $this->resolved[self::DESCRIPTIONS] ??= $this->entries(self::DESCRIPTIONS, trim: false);
    }

    /**
     * The `&&` from the original: one missing file disables the other list too.
     */
    private function bothListsPresent(): bool
    {
        return is_file($this->path(self::DOMAINS)) && is_file($this->path(self::URLS));
    }

    private function path(string $name): string
    {
        return ($this->directory ?? config_path()) . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @return array<string, true> entry => true, for isset() lookups
     */
    private function entries(string $name, bool $trim, bool $dropComments = false): array
    {
        $path = $this->path($name);

        clearstatcache(true, $path);

        if (!is_file($path)) {
            return [];
        }

        $stamp = filemtime($path) . ":" . filesize($path);

        if ((self::$files[$path]["stamp"] ?? null) === $stamp) {
            return self::$files[$path]["entries"];
        }

        $lines = explode("\n", (string) file_get_contents($path));

        if ($trim) {
            $lines = array_map("trim", $lines);
        }

        if ($dropComments) {
            $lines = array_filter($lines, fn(string $line): bool => strpos(trim($line), "#") !== 0);
        }

        // A set rather than a list: this is what turns three linear scans per
        // result into three hash lookups. A repeated line collapses onto itself,
        // which is what a set should do with one.
        $entries = array_fill_keys($lines, true);

        self::$files[$path] = ["stamp" => $stamp, "entries" => $entries];

        return $entries;
    }
}
