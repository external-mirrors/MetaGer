<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The privacy policy sends people to /kontakt for email, so the address lives
 * in one place. That page has had no contact form since the removal-requests
 * MR, but the policy still called the link "contact form", and in six
 * languages the link had no text at all — machine translation had moved the
 * words out of the <a>.
 */
class PrivacyPolicyContactLinkTest extends TestCase
{
    /** @return array<string, array{0: string}> */
    public static function locales(): array
    {
        $locales = [];
        foreach (glob(dirname(__DIR__, 2) . "/lang/*/privacy.php") as $file) {
            // de-DE, en-GB, … are symlinks the container creates to these.
            if (is_link(dirname($file))) {
                continue;
            }
            $locale = basename(dirname($file));
            $locales[$locale] = [$locale];
        }
        return $locales;
    }

    #[DataProvider("locales")]
    public function testTheContactLinksHaveText(string $locale): void
    {
        $responsible = trans("privacy.responsible_party.description", ["link_impress" => "/i", "link_contact" => "/k"], $locale);
        $rights = trans("privacy.rights.perception", ["contact_link" => "/k"], $locale);

        $this->assertMatchesRegularExpression('#<a href="/k">[^<\s][^<]*</a>#', $responsible);
        $this->assertMatchesRegularExpression('#<a href="/k">[^<\s][^<]*</a>#', $rights);
        $this->assertStringNotContainsString("</b>", $rights, "A closing </b> with no opening tag.");
    }

    public function testTheGermanPolicyLinksTheContactPageNotAForm(): void
    {
        $this->get("/de-DE/datenschutz")
            ->assertOk()
            ->assertSee('">Kontaktseite</a>', false)
            ->assertDontSee('">Kontaktformular</a>', false);
    }

    public function testTheEnglishPolicyLinksTheContactPageNotAForm(): void
    {
        $this->followingRedirects()->get("/en-US/datenschutz", ["Sec-Fetch-Mode" => "navigate"])
            ->assertOk()
            ->assertSee('">contact page</a>', false)
            ->assertDontSee('">contact form</a>', false);
    }
}
