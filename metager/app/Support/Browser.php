<?php

namespace App\Support;

use DeviceDetector\Cache\LaravelCache;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use DeviceDetector\Yaml\Pecl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Browser and device facts about the current client.
 *
 * One detector for the whole application. There used to be three —
 * jenssegers/agent (unmaintained since 2020, and emitting PHP 8.4 deprecations
 * through mobiledetectlib), foroco/php-browser-detection, and
 * matomo/device-detector — each with its own vocabulary, each parsing the same
 * User-Agent again. This wraps the one that is actually maintained.
 *
 * Names are normalised to the short forms the views have always branched on
 * ("Edge", not "Microsoft Edge"), so the view logic did not have to change
 * shape when the library underneath did.
 *
 * ## Why the answers are cached, and not the regexes
 *
 * DeviceDetector is handed a LaravelCache, which caches the regex lists it
 * parses out of its YAML files. That is worth having — without it a parse costs
 * about 110 ms, all of it in a pure-PHP YAML parser — but it is not enough:
 * with those lists cached a single parse still costs ~2.6 ms, being twenty-six
 * round trips to fetch the lists plus some three thousand preg_match calls
 * against one User-Agent string.
 *
 * So what gets cached here is the handful of facts the application actually
 * asks for, keyed by the User-Agent and client hints that produced them. One
 * cache read replaces the lot. User-Agent strings repeat heavily across a
 * search engine's traffic, so the hit rate is close to one.
 *
 * The key carries DeviceDetector::VERSION, so upgrading the library retires
 * every cached answer rather than serving last version's opinion. The TTL is
 * deliberately short for something this immutable: the User-Agent is
 * client-controlled, so a flood of distinct ones would otherwise take up room
 * in a cache that is shared with search results and evicts by LRU. An hour is
 * far longer than a real User-Agent needs to be seen twice.
 */
class Browser
{
    /**
     * DeviceDetector's names -> the short names used across the views.
     *
     * Anything not listed resolves to null, which the plugin page treats as an
     * unrecognised browser.
     */
    private const NAMES = [
        "Firefox" => "Firefox",
        "Firefox Mobile" => "Firefox",
        "Firefox Mobile iOS" => "Firefox",
        "Chrome" => "Chrome",
        "Chrome Mobile" => "Chrome",
        "Chrome Mobile iOS" => "Chrome",
        "Microsoft Edge" => "Edge",
        "Safari" => "Safari",
        "Mobile Safari" => "Safari",
        "Opera" => "Opera",
        "Opera Mobile" => "Opera",
        "Internet Explorer" => "IE",
        "UC Browser" => "UCBrowser",
    ];

    private const CACHE_TTL = 3600;

    /**
     * @param array<string, mixed> $facts
     */
    private function __construct(private readonly array $facts)
    {
    }

    public static function fromUserAgent(?string $userAgent, array $clientHints = []): self
    {
        $userAgent ??= "";
        $hints = ClientHints::factory($clientHints);
        $key = self::cacheKey($userAgent, $hints);

        $facts = Cache::get($key);

        if (!is_array($facts)) {
            $facts = self::detect($userAgent, $hints);
            Cache::put($key, $facts, self::CACHE_TTL);
        }

        return new self($facts);
    }

    public static function fromRequest(?Request $request = null): self
    {
        $request ??= request();

        return self::fromUserAgent($request->userAgent(), $_SERVER);
    }

    /**
     * Keyed off the ClientHints object rather than the headers it came from.
     * Those headers arrive as the whole of $_SERVER, most of which the hints
     * ignore; keying on the input would give two identical clients two cache
     * entries for the sake of an environment variable.
     */
    private static function cacheKey(string $userAgent, ClientHints $hints): string
    {
        return "browser:" . sha1(DeviceDetector::VERSION . "\0" . $userAgent . "\0" . serialize($hints));
    }

    /**
     * The full parse — everything below this line reads the result of one of
     * these, never a detector.
     *
     * @return array<string, mixed>
     */
    private static function detect(string $userAgent, ClientHints $hints): array
    {
        $detector = new DeviceDetector($userAgent, $hints);
        $detector->setCache(new LaravelCache());

        // DeviceDetector's own default is a bundled pure-PHP YAML parser, and
        // reading its ~3,000 detection regexes with it costs 98 ms whenever the
        // parsed lists are not in the cache. libyaml does the same work in
        // 10 ms. Conditional because the extension is a property of the image
        // (see build/fpm/Dockerfile): without it this falls back to the
        // bundled parser and is merely slow, not broken.
        if (extension_loaded("yaml")) {
            $detector->setYamlParser(new Pecl());
        }

        $detector->parse();

        return [
            // The library's own name, not the short one: NAMES is applied on
            // read, so adding a browser to that map does not have to wait for
            // every cached answer to expire.
            "client" => $detector->getClient("name"),
            "version" => $detector->getClient("version"),
            "engine" => $detector->getClient("engine"),
            "engine_version" => $detector->getClient("engine_version"),
            "os" => $detector->getOs("name"),
            "desktop" => $detector->isDesktop(),
            "mobile" => $detector->isMobile(),
            "tablet" => $detector->isTablet(),
        ];
    }

    /**
     * Short browser name, or null when the browser is not one we branch on.
     */
    public function name(): ?string
    {
        $name = $this->facts["client"];

        if (!is_string($name)) {
            return null;
        }

        return self::NAMES[$name] ?? null;
    }

    public function version(): ?string
    {
        return $this->stringOrNull($this->facts["version"]);
    }

    public function engine(): ?string
    {
        return $this->stringOrNull($this->facts["engine"]);
    }

    /**
     * DeviceDetector reports an unknown value as the literal "UNK" rather than
     * an empty string, which would otherwise leak into version comparisons.
     */
    private function stringOrNull(mixed $value): ?string
    {
        if (!is_string($value) || $value === "" || $value === "UNK") {
            return null;
        }

        return $value;
    }

    /**
     * Gecko engine version, or 0.0 when this is not a Gecko browser.
     *
     * Replaces foroco's browser_gecko_version, which the settings page and the
     * suggestion-delay logic both branch on.
     */
    public function geckoVersion(): float
    {
        return $this->engineVersionFor("Gecko");
    }

    /**
     * Chromium engine version, or 0.0 when this is not a Chromium browser.
     *
     * Replaces foroco's browser_chromium_version. DeviceDetector reports the
     * engine as "Blink", which is Chromium's.
     */
    public function chromiumVersion(): float
    {
        return $this->engineVersionFor("Blink");
    }

    private function engineVersionFor(string $engine): float
    {
        if ($this->engine() !== $engine) {
            return 0.0;
        }

        return (float) ($this->facts["engine_version"] ?: 0);
    }

    public function platform(): ?string
    {
        $os = $this->facts["os"];

        return is_string($os) && $os !== "" ? $os : null;
    }

    /**
     * Whether the client runs the given platform.
     *
     * Substring rather than equality because DeviceDetector spells Linux
     * "GNU/Linux", while the views ask for "Linux".
     */
    public function isPlatform(string $platform): bool
    {
        $os = $this->platform();

        return $os !== null && stripos($os, $platform) !== false;
    }

    public function isDesktop(): bool
    {
        return $this->facts["desktop"];
    }

    public function isMobile(): bool
    {
        return $this->facts["mobile"];
    }

    public function isTablet(): bool
    {
        return $this->facts["tablet"];
    }

    public function isPhone(): bool
    {
        return $this->facts["mobile"] && !$this->facts["tablet"];
    }

    public function deviceType(): string
    {
        if ($this->isTablet()) {
            return "tablet";
        }

        return $this->isMobile() ? "mobile" : "desktop";
    }
}
