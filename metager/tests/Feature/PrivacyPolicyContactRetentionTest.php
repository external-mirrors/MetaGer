<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Lang;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * What the privacy policy promises about mail to us, pinned to what suma-crm
 * does with it: a ticket and its mails and attachments go three months after
 * it closed (`crm:prune-tickets`, the support group's `retention_months`).
 * If that period changes in suma-crm, this text has to change too.
 *
 * The policy had a section on removal requests through suma-crm's form. We
 * take none (decided 2026-11-11) and the form is switched off, so the section
 * is gone; it comes back with the form, if the form ever does.
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

    public function testThereIsNoSectionOnRemovalRequests(): void
    {
        $this->get("/de-DE/datenschutz")
            ->assertOk()
            ->assertDontSee('id="removal-requests"', false)
            ->assertDontSeeText("Anträge auf Entfernen von Suchergebnissen")
            ->assertDontSeeText("my.suma-ev.de")
            ->assertDontSeeText("Ausweiskopie");
    }

    public function testTheEnglishPolicyHasNoSectionEither(): void
    {
        $this->followingRedirects()->get("/en-US/datenschutz", ["Sec-Fetch-Mode" => "navigate"])
            ->assertOk()
            ->assertSeeText("Contact by email")
            ->assertDontSeeText("Requests to remove search results");
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

    /** No language keeps the removal section's text, or the old contact form's. */
    #[DataProvider("locales")]
    public function testEveryLanguageHasItsOwnText(string $locale): void
    {
        $this->assertFalse(Lang::hasForLocale("privacy.contexts.removal", $locale), "[$locale] still has privacy.contexts.removal");
        $this->assertStringContainsString("3", trans("privacy.contexts.contact.contact", [], $locale));
        $this->assertStringNotContainsString("2", trans("privacy.contexts.contact.contact", [], $locale));
    }
}
