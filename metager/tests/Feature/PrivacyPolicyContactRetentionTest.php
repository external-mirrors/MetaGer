<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Lang;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * What the privacy policy promises about mail to us and removal requests,
 * pinned to what suma-crm does with them:
 *
 * - a ticket and its mails and attachments go three months after it closed
 *   (`crm:prune-tickets`, the support group's `retention_months`);
 * - a removal case forgets who asked six months after closing, its ID files
 *   included (`crm:anonymise-removal-requests`, `REMOVAL_RETENTION_MONTHS`);
 * - a blocked URL keeps its terms (in the blocklist service) and the reason
 *   for the decision while the block exists.
 *
 * If one of those periods changes in suma-crm, this text has to change too.
 */
class PrivacyPolicyContactRetentionTest extends TestCase
{
    public function testMailToUsIsKeptThreeMonthsNotTheOldFormsTwo(): void
    {
        $this->get("/de-DE/datenschutz")
            ->assertOk()
            ->assertSeeText("Kontakt per E-Mail")
            ->assertSeeText("bis 3 Monate nach Abschluss Ihres Anliegens")
            ->assertDontSeeText("Kontaktformular")
            ->assertDontSeeText("2 Monate nach Abschluss Ihres Anliegens");
    }

    public function testRemovalRequestsHaveTheirOwnSection(): void
    {
        $content = $this->get("/de-DE/datenschutz")->assertOk()->getContent();

        $start = strpos($content, 'id="removal-requests"');
        $this->assertNotFalse($start, "No section on removal requests.");
        $section = substr($content, $start, strpos($content, "</article>", $start) - $start);

        $this->assertStringContainsString('href="' . route("removal") . '"', $section);
        $this->assertStringContainsString("6 Monate nach Abschluss des Vorgangs", $section);
        $this->assertStringContainsString("Ausweiskopie", $section);
        $this->assertStringContainsString("solange das Ergebnis ausgeblendet bleibt", $section);
        $this->assertStringContainsString("Art. 17 Abs. 3 lit. e", $section);
    }

    /**
     * Version C: the form on my.suma-ev.de, the ID upload (encrypted, gone
     * with the case) and the blocklist service that holds what is hidden.
     * A form never sent goes after a week (`crm:prune-removal-drafts`).
     */
    public function testTheSectionCoversTheFormTheUploadAndTheBlocklistService(): void
    {
        $content = $this->get("/de-DE/datenschutz")->assertOk()->getContent();
        $start = strpos($content, 'id="removal-requests"');
        $section = substr($content, $start, strpos($content, "</article>", $start) - $start);

        $this->assertStringContainsString("my.suma-ev.de", $section);
        $this->assertStringContainsString("Ausweiskopie speichern wir verschlüsselt", $section);
        $this->assertStringContainsString("Sperrlisten-Dienst", $section);
        $this->assertStringContainsString("nach 7 Tagen", $section);
    }

    public function testTheEnglishPolicyHasTheSectionToo(): void
    {
        $this->followingRedirects()->get("/en-US/datenschutz", ["Sec-Fetch-Mode" => "navigate"])
            ->assertOk()
            ->assertSeeText("Contact by email")
            ->assertSeeText("Requests to remove search results")
            ->assertSeeText("for as long as the result stays hidden");
    }

    /** @return array<string, array{0: string}> */
    public static function locales(): array
    {
        $locales = [];
        foreach (glob(dirname(__DIR__, 2) . "/lang/*/privacy.php") as $file) {
            if (!is_link(dirname($file))) {
                $locale = basename(dirname($file));
                $locales[$locale] = [$locale];
            }
        }
        return $locales;
    }

    /** No language falls back to English, or keeps the old form's text. */
    #[DataProvider("locales")]
    public function testEveryLanguageHasItsOwnText(string $locale): void
    {
        foreach (["title", "description", "contact", "message", "block"] as $key) {
            $this->assertTrue(Lang::hasForLocale("privacy.contexts.removal.$key", $locale), "[$locale] lacks privacy.contexts.removal.$key");
        }
        $this->assertStringContainsString("3", trans("privacy.contexts.contact.contact", [], $locale));
        $this->assertStringNotContainsString("2", trans("privacy.contexts.contact.contact", [], $locale));
    }
}
