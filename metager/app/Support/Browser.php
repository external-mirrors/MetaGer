<?php

namespace App\Support;

use DeviceDetector\Cache\LaravelCache;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use Illuminate\Http\Request;

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

    private function __construct(private readonly DeviceDetector $detector)
    {
    }

    public static function fromUserAgent(?string $userAgent, array $clientHints = []): self
    {
        $detector = new DeviceDetector(
            $userAgent ?? "",
            ClientHints::factory($clientHints)
        );
        $detector->setCache(new LaravelCache());
        $detector->parse();

        return new self($detector);
    }

    public static function fromRequest(?Request $request = null): self
    {
        $request ??= request();

        return self::fromUserAgent($request->userAgent(), $_SERVER);
    }

    /**
     * Short browser name, or null when the browser is not one we branch on.
     */
    public function name(): ?string
    {
        $name = $this->detector->getClient("name");

        if (!is_string($name)) {
            return null;
        }

        return self::NAMES[$name] ?? null;
    }

    public function version(): ?string
    {
        return $this->stringOrNull($this->detector->getClient("version"));
    }

    public function engine(): ?string
    {
        return $this->stringOrNull($this->detector->getClient("engine"));
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

        return (float) ($this->detector->getClient("engine_version") ?: 0);
    }

    public function platform(): ?string
    {
        $os = $this->detector->getOs("name");

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
        return $this->detector->isDesktop();
    }

    public function isMobile(): bool
    {
        return $this->detector->isMobile();
    }

    public function isTablet(): bool
    {
        return $this->detector->isTablet();
    }

    public function isPhone(): bool
    {
        return $this->detector->isMobile() && !$this->detector->isTablet();
    }

    public function deviceType(): string
    {
        if ($this->isTablet()) {
            return "tablet";
        }

        return $this->isMobile() ? "mobile" : "desktop";
    }
}
