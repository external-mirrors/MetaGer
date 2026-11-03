<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * /kontakt — what the page has to keep saying, whatever it looks like.
 *
 * Written before the dead contact form came out, to pin the parts of the page
 * that are not dead: the ticket address for the visitor's language, the PGP
 * key and fingerprint, the postal address, and the optional trailing segment
 * old links still carry (`/kontakt/` from the sidebar, `/kontakt/<url>` from
 * the long-gone "page missing" hint).
 */
class ContactPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            "metager.metager.ticketsystem.germanmail" => "kontakt@example.org",
            "metager.metager.ticketsystem.englishmail" => "contact@example.org",
        ]);
    }

    public function testGermanVisitorsGetTheGermanTicketAddress(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertSee('href="mailto:kontakt@example.org"', false)
            ->assertDontSee("contact@example.org", false);
    }

    public function testEnglishVisitorsGetTheEnglishTicketAddress(): void
    {
        // en-US is the default locale and redirected out of the path.
        $this->followingRedirects()->get("/en-US/kontakt")
            ->assertOk()
            ->assertSee('href="mailto:contact@example.org"', false)
            ->assertDontSee("kontakt@example.org", false);
    }

    public function testThePgpKeyIsOfferedWithItsFingerprint(): void
    {
        $page = $this->get("/de-DE/kontakt")->assertOk();

        $page->assertSee('href="/download/pubkey.asc"', false);
        $page->assertSee("https://keys.openpgp.org/search?q=" . urlencode("kontakt@example.org"), false);
        $page->assertSee("5FA5 2398 C382 B498 B14A", false);
    }

    public function testThePostalAddressIsShown(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertSee("Postfach 51 01 43", false)
            ->assertSee("D-30631 Hannover", false);
    }

    /** @return array<string, array{0: string}> */
    public static function trailingSegments(): array
    {
        return [
            "the sidebar's trailing slash" => ["/de-DE/kontakt/"],
            "a word" => ["/de-DE/kontakt/anything"],
            "a host, without a locale prefix" => ["/kontakt/example.org"],
        ];
    }

    /**
     * `kontakt/{url?}`: the sidebar links to `/kontakt/` and the old form
     * linked to `/kontakt/<something>`. Both must still land on the page.
     *
     * One request per case: a second request inside the same test is routed
     * against the first one's locale-stripped state and 404s, which is a
     * harness artefact and not what a visitor gets.
     */
    #[DataProvider("trailingSegments")]
    public function testATrailingSegmentStillLandsOnThePage(string $path): void
    {
        $this->get($path)->assertOk()->assertSee("Postfach 51 01 43", false);
    }

    /**
     * The anonymous contact form was commented out years ago, but the page
     * kept rendering an always-on "we have difficulties" alert above where it
     * used to be, and loaded resources/js/contact.js, which reached for the
     * form's `pcsrf` input and threw on null under webdriver.
     */
    public function testThereIsNoContactFormLeft(): void
    {
        $page = $this->get("/de-DE/kontakt")->assertOk();

        $page->assertDontSee("<form", false);
        $page->assertDontSee("pcsrf", false);
        $page->assertDontSee("kontakt.form", false);
        $page->assertDontSeeText("Wir haben derzeit Schwierigkeiten");
        $page->assertDontSee("contact.js", false);
    }

    public function testTheDeadContactScriptIsNoLongerBuilt(): void
    {
        $this->assertFileDoesNotExist(resource_path("js/contact.js"));
        $this->assertStringNotContainsString("contact.js", file_get_contents(base_path("vite.config.js")));
    }

    /**
     * The most frequent mail we cannot act on is "please remove this result":
     * MetaGer shows other engines' results. The page must not advertise that
     * request — no headline, no call to action at the top — but whoever is
     * about to write it meets a pointer to the removal guide right before the
     * button that opens their mail program. Everyone else just writes.
     */
    public function testTheRemovalGuideIsPointedToRightBeforeWriting(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $hint = strpos($content, '<p class="contact-removal-hint">');
        $guide = strpos($content, '/de-DE/entfernen"');
        $button = strpos($content, '<a class="contact-button" href="mailto:');

        $this->assertNotFalse($hint, "There is no removal hint on the contact page.");
        $this->assertNotFalse($guide, "The contact page does not link to the removal guide.");
        $this->assertNotFalse($button);
        $this->assertLessThan($guide, $hint);
        $this->assertLessThan($button, $guide, "The guide has to come before the button that opens the mail program.");
        $this->assertSame(
            1,
            substr_count($content, '/de-DE/entfernen"'),
            "The guide is linked once, from the hint — not from a call to action of its own.",
        );
    }

    /** Characterization of what the first redesign did, and must not come back. */
    public function testTheRemovalRequestIsNotAdvertised(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertDontSeeText("Ein Suchergebnis entfernen lassen?")
            ->assertDontSeeText("Anleitung: Suchergebnis entfernen")
            ->assertDontSee("contact-removal\"", false);
    }

    /** Email is what most visitors came for: it comes before the topic list. */
    public function testEmailComesFirst(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $email = strpos($content, 'id="email"');
        $topics = strpos($content, 'contact-topics"');
        $post = strpos($content, 'id="post"');

        $this->assertNotFalse($email);
        $this->assertNotFalse($topics);
        $this->assertNotFalse($post);
        $this->assertLessThan($topics, $email);
        $this->assertLessThan($post, $topics);
    }

    /**
     * The address is what most visitors came for, so it is the email
     * section's headline and comes with a button that opens their mail program —
     * not a link inside a sentence under eight lines of PGP explanation.
     */
    public function testTheAddressIsTheEmailCardsMainAction(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '#<p class="contact-address">\s*kontakt@example\.org\s*</p>#',
            $content,
        );
        $this->assertMatchesRegularExpression(
            '#<a class="contact-button" href="mailto:kontakt@example\.org">E-Mail schreiben</a>#',
            $content,
        );
    }

    /**
     * PGP is for the few who use it. It stays complete — key, keyserver,
     * fingerprint — but folded, and the fold opens without script.
     */
    public function testPgpDetailsAreFoldedAway(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $fold = strpos($content, '<details class="contact-pgp">');
        $this->assertNotFalse($fold, "The PGP details are not in a <details>.");
        $this->assertStringContainsString("<summary>Verschlüsselt schreiben (PGP)</summary>", $content);

        $end = strpos($content, "</details>", $fold);
        $folded = substr($content, $fold, $end - $fold);
        $this->assertStringContainsString('href="/download/pubkey.asc"', $folded);
        $this->assertStringContainsString("5FA5 2398 C382 B498 B14A", $folded);
    }

    /**
     * The fold says how suma-crm actually decides to answer encrypted
     * (App\Pgp\KeyDiscovery there): the sender signs; the key comes from the
     * mail or keys.openpgp.org; it is used by itself only when
     * keys.openpgp.org serves it for the address or the mail passed DKIM for
     * its From domain, otherwise after a person confirms it; the first such
     * key is trusted and a later one is not swapped in silently; and a reply
     * to an encrypted mail never goes out in clear text. If suma-crm's rules
     * change, this text has to change with them.
     */
    public function testPgpFoldExplainsWhenWeAnswerEncrypted(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();
        $fold = strpos($content, '<details class="contact-pgp">');
        $folded = substr($content, $fold, strpos($content, "</details>", $fold) - $fold);

        $this->assertStringContainsString("signieren Sie Ihre E-Mail", $folded);
        $this->assertStringContainsString("Autocrypt", $folded);
        $this->assertStringContainsString("keys.openpgp.org ihn für Ihre Adresse ausgibt", $folded);
        $this->assertStringContainsString("DKIM", $folded);
        $this->assertStringContainsString("Trust on First Use", $folded);
        $this->assertStringContainsString("nie unverschlüsselt", $folded);
        $this->assertStringNotContainsString("fügen Sie bitte Ihren öffentlichen Schlüssel an", $folded);
    }

    /** Post is welcome; the page just says email is faster. */
    public function testThePostalCardDoesNotTalkPeopleOutOfWriting(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertDontSeeText("unbedingt nötig")
            ->assertSeeText("Am schnellsten antworten wir per E-Mail.");
    }

    /** @return array<string, array{0: string}> */
    public static function topicPaths(): array
    {
        return [
            "key" => ["/de-DE/hilfe/schluessel"],
            "orders" => ["/de-DE/konto/bestellungen"],
            "donation" => ["/de-DE/spende"],
            "help" => ["/de-DE/hilfe"],
            "privacy" => ["/de-DE/datenschutz"],
        ];
    }

    #[DataProvider("topicPaths")]
    public function testFrequentQuestionsLinkToTheirPages(string $path): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertSee($path . '"', false);
    }

    public function testThePageNeedsNoScriptOfItsOwn(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();
        $start = strpos($content, '<div id="kontakt">');
        $end = strpos($content, '</main>', $start);

        $this->assertNotFalse($start);
        $this->assertStringNotContainsString("<script", substr($content, $start, $end === false ? null : $end - $start));
    }
}
