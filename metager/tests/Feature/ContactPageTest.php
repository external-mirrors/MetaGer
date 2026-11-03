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
     * MetaGer shows other engines' results. The page sends people to the
     * removal guide before it shows an address.
     */
    public function testTheRemovalGuideComesBeforeAnyAddress(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $guide = strpos($content, '/de-DE/entfernen"');
        $mail = strpos($content, 'href="mailto:');

        $this->assertNotFalse($guide, "The contact page does not link to the removal guide.");
        $this->assertNotFalse($mail);
        $this->assertLessThan($mail, $guide, "The removal guide has to come before the email address.");
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
