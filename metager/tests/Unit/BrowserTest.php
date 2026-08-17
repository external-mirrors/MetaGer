<?php

namespace Tests\Unit;

use App\Support\Browser;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Extends Tests\TestCase rather than PHPUnit's: DeviceDetector is handed a
 * LaravelCache, which needs the container.
 */
class BrowserTest extends TestCase
{
    private const FIREFOX = "Mozilla/5.0 (X11; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0";
    private const CHROME = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36";
    private const EDGE = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0";
    private const SAFARI = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15";
    private const OPERA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 OPR/112.0.0.0";
    private const IE11 = "Mozilla/5.0 (Windows NT 10.0; Trident/7.0; rv:11.0) like Gecko";
    private const UC_MOBILE = "Mozilla/5.0 (Linux; U; Android 10; en-US) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/78.0.3904.108 UCBrowser/13.4.0.1306 Mobile Safari/537.36";
    private const FIREFOX_ANDROID = "Mozilla/5.0 (Android 14; Mobile; rv:128.0) Gecko/128.0 Firefox/128.0";

    /**
     * @return array<string, array{0: string, 1: string|null}>
     */
    public static function browserNames(): array
    {
        return [
            "firefox" => [self::FIREFOX, "Firefox"],
            "firefox on android normalises to Firefox" => [self::FIREFOX_ANDROID, "Firefox"],
            "chrome" => [self::CHROME, "Chrome"],
            "edge is shortened from Microsoft Edge" => [self::EDGE, "Edge"],
            "safari" => [self::SAFARI, "Safari"],
            "opera" => [self::OPERA, "Opera"],
            "internet explorer is shortened to IE" => [self::IE11, "IE"],
            "uc browser loses its space" => [self::UC_MOBILE, "UCBrowser"],
            "unrecognised browser is null" => ["SomeBrowserWeDoNotKnow/1.0", null],
            "empty user agent is null" => ["", null],
        ];
    }

    #[DataProvider("browserNames")]
    public function testNameIsNormalisedToTheShortForm(string $userAgent, ?string $expected): void
    {
        $this->assertSame($expected, Browser::fromUserAgent($userAgent)->name());
    }

    public function testNullUserAgentIsHandled(): void
    {
        $browser = Browser::fromUserAgent(null);

        $this->assertNull($browser->name());
        $this->assertNull($browser->version());
    }

    public function testVersionIsReported(): void
    {
        $this->assertSame("128.0", Browser::fromUserAgent(self::FIREFOX)->version());
        $this->assertSame("126.0", Browser::fromUserAgent(self::CHROME)->version());
    }

    public function testGeckoVersionIsOnlySetForGeckoBrowsers(): void
    {
        $this->assertSame(128.0, Browser::fromUserAgent(self::FIREFOX)->geckoVersion());
        $this->assertSame(0.0, Browser::fromUserAgent(self::CHROME)->geckoVersion());
        $this->assertSame(0.0, Browser::fromUserAgent(self::SAFARI)->geckoVersion());
    }

    public function testChromiumVersionIsOnlySetForBlinkBrowsers(): void
    {
        $this->assertGreaterThan(0, Browser::fromUserAgent(self::CHROME)->chromiumVersion());
        $this->assertGreaterThan(0, Browser::fromUserAgent(self::EDGE)->chromiumVersion());
        $this->assertSame(0.0, Browser::fromUserAgent(self::FIREFOX)->chromiumVersion());
        $this->assertSame(0.0, Browser::fromUserAgent(self::SAFARI)->chromiumVersion());
    }

    public function testDeviceTypeAndPredicates(): void
    {
        $desktop = Browser::fromUserAgent(self::FIREFOX);
        $this->assertTrue($desktop->isDesktop());
        $this->assertFalse($desktop->isMobile());
        $this->assertFalse($desktop->isPhone());
        $this->assertSame("desktop", $desktop->deviceType());

        $phone = Browser::fromUserAgent(self::UC_MOBILE);
        $this->assertFalse($phone->isDesktop());
        $this->assertTrue($phone->isMobile());
        $this->assertTrue($phone->isPhone());
        $this->assertSame("mobile", $phone->deviceType());
    }

    /**
     * DeviceDetector spells it "GNU/Linux" while the plugin views ask for
     * "Linux", so the match is a substring one.
     */
    public function testPlatformMatchIsSubstringBased(): void
    {
        $linux = Browser::fromUserAgent(self::FIREFOX);
        $this->assertTrue($linux->isPlatform("Linux"));
        $this->assertFalse($linux->isPlatform("Windows"));

        $this->assertTrue(Browser::fromUserAgent(self::CHROME)->isPlatform("Windows"));
    }
}
