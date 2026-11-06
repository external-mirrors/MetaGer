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
     * Nearly everyone comes for the address, so it is there without a click.
     * The choice above it is the one place a removal request turns off that
     * path: without script, it swaps the mail, phone and post rows for the
     * removal row (Browser/ProgressiveEnhancementTest does the swapping).
     */
    public function testTheReasonForContactIsChosenAboveTheAddressWhichIsShownByDefault(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $chooser = strpos($content, '<fieldset class="contact-reason">');
        $general = strpos($content, '<div class="contact-general">');
        $this->assertNotFalse($chooser, "The contact page has no choice of reason.");
        $this->assertLessThan($general, $chooser);
        $this->assertMatchesRegularExpression('#<input[^>]*id="contact-reason-general"[^>]*\\schecked#', $content);
        $this->assertDoesNotMatchRegularExpression('#<input[^>]*id="contact-reason-removal"[^>]*\\schecked#', $content);
        $this->assertStringContainsString('class="contact-address"', substr($content, $general));
    }

    /**
     * Removal requests are only taken through the form, but people read the
     * guide first: it says where a removal works and leads to the form at its
     * end. So this row sends them to the guide and never to the form itself.
     */
    public function testARemovalRequestIsSentToTheGuideNeverDirectlyToTheForm(): void
    {
        config(["metager.metager.crm.url" => "https://my.suma-ev.de"]);

        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $removal = strpos($content, '<section class="contact-section contact-removal"');
        $this->assertNotFalse($removal, "The contact page has no removal row.");
        $row = substr($content, $removal, strpos($content, "</section>", $removal) - $removal);

        $this->assertStringContainsString("nur über unser Formular", $row);
        $this->assertStringContainsString("E-Mail, Telefon oder Post", $row);
        $this->assertMatchesRegularExpression('#<a class="contact-button" href="[^"]*/de-DE/entfernen">#', $row);
        $this->assertStringNotContainsString("/entfernung", $content, "The form is only reached through the guide.");
    }


    /**
     * Email is what most visitors came for, so it comes first; the ways to
     * reach us stay together (email, phone, post), and the topic list follows.
     */
    public function testTheWaysToReachUsComeFirstAndTogether(): void
    {
        $content = $this->get("/de-DE/kontakt")->assertOk()->getContent();

        $order = [];
        foreach (['id="email"', 'id="phone"', 'id="post"', 'contact-topics"'] as $marker) {
            $position = strpos($content, $marker);
            $this->assertNotFalse($position, "Missing $marker on the contact page.");
            $order[] = $position;
        }

        $sorted = $order;
        sort($sorted);
        $this->assertSame($sorted, $order, "Expected email, phone, post, then the topic list.");
    }

    /** The number from the imprint (impressum.info.3), callable from a phone. */
    public function testThePhoneNumberIsShownAndCallable(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertSee('<a href="tel:+4951134000070">+49 511 34000070</a>', false);
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

    /**
     * Post is welcome; the page says once, under the title, that email is
     * faster — not again inside the postal section, where it read as talking
     * people out of writing a letter.
     */
    public function testThePostalCardDoesNotTalkPeopleOutOfWriting(): void
    {
        $content = $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertDontSeeText("unbedingt nötig")
            ->assertSee('<p class="contact-lead">Am schnellsten antworten wir per E-Mail.</p>', false)
            ->getContent();

        $this->assertSame(1, substr_count($content, "Am schnellsten antworten wir per E-Mail."));
        $this->assertLessThan(strpos($content, 'id="email"'), strpos($content, 'class="contact-lead"'));
    }

    /**
     * The address-card layout: each way to reach us is one row, its name a
     * short label beside it. Long labels ("Per Brief-Post") wrapped in the
     * label column, so the label is the bare medium.
     */
    public function testEachWayToReachUsIsLabelledShortly(): void
    {
        $this->get("/de-DE/kontakt")
            ->assertOk()
            ->assertSee('<h2 id="contact-email-heading">E-Mail</h2>', false)
            ->assertSee('<h2 id="contact-phone-heading">Telefon</h2>', false)
            ->assertSee('<h2 id="contact-post-heading">Post</h2>', false)
            ->assertDontSeeText("Per Brief-Post");
    }

    /**
     * The address and the button that writes to it sit together, so the
     * button reads as "write to this address" rather than as a separate
     * call to action.
     */
    public function testTheAddressAndItsButtonSitTogether(): void
    {
        $this->assertMatchesRegularExpression(
            '#<div class="contact-email-primary">\s*<p class="contact-address">[^<]*</p>\s*<a class="contact-button" href="mailto:#',
            $this->get("/de-DE/kontakt")->assertOk()->getContent(),
        );
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
