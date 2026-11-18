<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Tests\TestCase;

/**
 * The help section on "Anonym öffnen" (/hilfe/datensicherheit#h-proxy) describes SafeBrowse as it
 * runs: sessions, the two views, the page view without JavaScript, and its limits. The section
 * before it described the old proxy as the fallback.
 */
class AnonymousOpeningHelpTest extends TestCase
{
    private const PARTS = ['use', 'session', 'views', 'nojs', 'limits'];

    /** @return array<string, array{0: string}> */
    public static function locales(): array
    {
        $locales = [];
        foreach (glob(__DIR__ . '/../../lang/*/help/help-privacy-protection.php') as $file) {
            $locale = basename(dirname($file, 2));
            $locales[$locale] = [$locale];
        }
        return $locales;
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('locales')]
    public function testEveryLanguageHasTheWholeSection(string $locale): void
    {
        $proxy = (include base_path("lang/$locale/help/help-privacy-protection.php"))['proxy'];

        $this->assertNotEmpty($proxy['title'] ?? null);
        $this->assertNotEmpty($proxy['intro'] ?? null);
        foreach (self::PARTS as $part) {
            $this->assertNotEmpty($proxy[$part]['title'] ?? null, "$locale has no $part title.");
            $this->assertNotEmpty($proxy[$part]['text'] ?? null, "$locale has no $part text.");
            $this->assertIsArray($proxy[$part]['text']);
        }
        $this->assertArrayNotHasKey('1', $proxy, "$locale still has the text about the old proxy.");
    }

    public function testThePageShowsTheSection(): void
    {
        $response = $this->get('/de-DE/hilfe/datensicherheit');

        $response->assertOk();
        $document = new DOMDocument();
        @$document->loadHTML($response->getContent());
        $section = (new DOMXPath($document))->query("//section[@id='h-proxy']")->item(0);
        $this->assertNotNull($section, 'The help index links to #h-proxy.');
        $this->assertSame(count(self::PARTS), $section->getElementsByTagName('h4')->length);
        $this->assertStringContainsString('Ohne JavaScript', $section->textContent);
        $this->assertStringNotContainsString('proxy.suma-ev.de', $response->getContent());
    }
}
