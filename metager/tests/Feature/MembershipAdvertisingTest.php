<?php

namespace Tests\Feature;

use App\Localization\LocaleContext;
use App\Support\MembershipOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Wo für die SUMA-EV-Mitgliedschaft geworben wird — und wo nicht.
 *
 * Eine Mitgliedschaft ist nur auf Deutsch zu haben: das Beitrittsformular ist
 * in keiner anderen Sprache übersetzt, ebenso wenig die Satzung, die
 * Beitragsordnung und die Seiten auf suma-ev.de, auf die es verweist. Ein
 * englischer oder polnischer Besucher, der auf „Mitglied werden“ klickt,
 * landet in einem Formular, das er nicht lesen kann.
 *
 * Zwei der sechs Stellen prüften die Sprache schon (Seitenmenü, /spende), vier
 * nicht — und das ist der Punkt dieses Tests: die Regel ist einfach, sie steht
 * jetzt an einer Stelle ({@see MembershipOffer}), und sie geht auf genau die
 * Art verloren, auf die sie schon zweimal verloren gegangen ist, nämlich
 * indem jemand einen neuen Hinweis einbaut und den Sprachvergleich nicht
 * kennt. Jede Stelle steht deshalb hier zweimal: einmal auf Deutsch gesehen,
 * einmal auf Englisch nicht gesehen.
 */
class MembershipAdvertisingTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    /** Der Keyserver kennt diesen Schlüssel und bietet drei Pakete an. */
    private function keyserverKnows(?string $membershipEnd = null): void
    {
        Http::preventStrayRequests();
        Http::fake([
            "*/api/json/price" => Http::response([
                "per_token" => 0.01,
                "vat" => 7,
                "purchasable" => [500, 1000, 2000],
            ]),
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "charge" => 248,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => 248, "expiration" => "2027-03-14 00:00:00"]],
                "key_config" => ["membershipEndDate" => $membershipEnd],
            ]),
        ]);
    }

    /** Angemeldet, so wie ein Browser es ist: über das Cookie. */
    private function signedIn(): self
    {
        return $this->withUnencryptedCookie("key", self::A_KEY);
    }

    /**
     * Die Oberflächensprache als `Accept-Language` und nicht als Pfadpräfix.
     *
     * `/en-US/about` gibt es nicht: auf `localhost` ist `en-US` die Sprache,
     * die keinen Präfix trägt ({@see \App\Localization\LocaleContext}), und
     * die Adresse mit Präfix antwortet 404 beziehungsweise 302. Der Header ist
     * die eine Eingabe, die für jede der elf Sprachen gleich aussieht.
     *
     * @return array<string, string>
     */
    private static function speaking(string $language): array
    {
        return ["Accept-Language" => "$language;q=0.9"];
    }

    // ── Die Regel selbst ─────────────────────────────────────────────────────

    /**
     * Verglichen wird die Sprache, nicht die Region: Österreich und die Schweiz
     * lesen dasselbe Formular wie Deutschland.
     */
    public function testTheRuleIsTheLanguageAndNotTheRegion(): void
    {
        foreach (["de-DE", "de-AT", "de-CH"] as $locale) {
            LocaleContext::resolve(Request::create("http://metager.de/$locale/"))->apply();
            $this->assertTrue(MembershipOffer::isAdvertised(), "$locale should be advertised to");
        }

        foreach (["en-US", "en-GB", "es-ES", "pl-PL", "nl-NL"] as $locale) {
            LocaleContext::resolve(Request::create("http://metager.de/$locale/"))->apply();
            $this->assertFalse(MembershipOffer::isAdvertised(), "$locale should not be advertised to");
        }
    }

    // ── Die Startseite ───────────────────────────────────────────────────────

    /**
     * Die Landingpage wirbt an zwei Stellen: in den drei Schritten („Mitglieder
     * suchen ohne weitere Kosten“) und in der Vereinskarte darunter, mit dem
     * Beitrittsformular und der Mitgliederseite auf suma-ev.de.
     */
    public function testTheLandingPageAdvertisesMembershipInGerman(): void
    {
        $response = $this->get("/", self::speaking("de-DE,de"))->assertOk();

        $response->assertSee("landing-steps__membership", false);
        $response->assertSee(route("membership_form"), false);
        $response->assertSee("https://suma-ev.de/mitglieder/", false);
    }

    public function testTheLandingPageDoesNotAdvertiseMembershipInEnglish(): void
    {
        $response = $this->get("/", self::speaking("en-US,en"))->assertOk();

        $response->assertDontSee("landing-steps__membership", false);
        $response->assertDontSee("https://suma-ev.de/mitglieder/", false);

        // Der Verein selbst bleibt stehen — nur der Weg in ein deutsches
        // Formular fällt weg.
        $response->assertSeeText(__("mg-story.ngo.title"));
        $response->assertSee("https://suma-ev.de/", false);
    }

    // ── Das Seitenmenü ───────────────────────────────────────────────────────

    public function testTheSidebarOffersMembershipInGermanOnly(): void
    {
        $this->get("/about", self::speaking("de-DE,de"))->assertOk()->assertSee("sidebar-img-member", false);
        $this->get("/about", self::speaking("en-US,en"))->assertOk()->assertDontSee("sidebar-img-member", false);
    }

    // ── Die Spendenseite ─────────────────────────────────────────────────────

    public function testTheDonationPageOffersMembershipInGermanOnly(): void
    {
        $this->get("/spende", self::speaking("de-DE,de"))->assertOk()->assertSee('id="membership-hint"', false);
        $this->get("/spende", self::speaking("en-US,en"))->assertOk()->assertDontSee('id="membership-hint"', false);
    }

    // ── Aufladen ─────────────────────────────────────────────────────────────

    /**
     * Der Hinweis, den der Keymanager an derselben Stelle zeigte und der beim
     * Umzug verloren ging: Wer gerade ein Token-Paket kaufen will, ist der
     * Einzige, den die Alternative „Mitglied werden“ überhaupt betrifft.
     */
    public function testTheChargeSectionOffersMembershipAsAnAlternative(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()->get("/konto", self::speaking("de-DE,de"))->assertOk();

        $response->assertSee('id="charge-membership"', false);
        $response->assertSeeText(__("account.page.charge.membership.heading", locale: "de"));
        $response->assertSeeText(__("account.page.charge.membership.action", locale: "de"));
        $response->assertSee(route("membership_form"), false);
    }

    public function testTheChargeSectionKeepsTheAlternativeToGerman(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()->get("/konto", self::speaking("en-US,en"))->assertOk();

        $response->assertDontSee('id="charge-membership"', false);
        $response->assertDontSeeText(__("account.page.charge.membership.heading", locale: "en"));

        // Die Pakete selbst sind unberührt — hier fällt nur die Alternative weg.
        $response->assertSee("account-tiers", false);
    }

    // ── Die Firmenmitgliedschaft ─────────────────────────────────────────────

    /**
     * Vier neue Stellen, und dieselbe Regel.
     *
     * Die Hinweise auf /firmen bewerben dieselbe Mitgliedschaft wie die sechs
     * darüber, nur den Zweig für Organisationen. Sie führen über /firmen in
     * dasselbe deutsche Formular und stehen deshalb unter derselben Bedingung —
     * das ist genau die Art, auf die diese Regel schon zweimal verloren
     * gegangen ist, und der Grund, warum die vier hier stehen und nicht in
     * {@see BusinessPageTest}.
     */
    public function testTheSidebarOffersTheBusinessPageInGermanOnly(): void
    {
        $this->get("/about", self::speaking("de-DE,de"))->assertOk()
            ->assertSee("sidebar-img-business", false)
            ->assertSee(route("business"), false);

        $this->get("/about", self::speaking("en-US,en"))->assertOk()
            ->assertDontSee("sidebar-img-business", false);
    }

    /**
     * Auf /preise, weil dort jemand ausrechnet, was MetaGer kostet — und wer
     * das für eine Organisation tut, rechnet mit dem falschen Modell.
     */
    public function testThePricePageOffersTheBusinessPageInGermanOnly(): void
    {
        $this->get("/preise", self::speaking("de-DE,de"))->assertOk()
            ->assertSee('id="price-business"', false)
            ->assertSeeText(__("business.hints.price.heading", locale: "de"));

        $this->get("/preise", self::speaking("en-US,en"))->assertOk()
            ->assertDontSee('id="price-business"', false);
    }

    /** Im Konto neben der Alternative für Einzelne, an derselben Kaufstelle. */
    public function testTheChargeSectionOffersTheBusinessPageInGermanOnly(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/konto", self::speaking("de-DE,de"))->assertOk()
            ->assertSee('id="charge-business"', false)
            ->assertSeeText(__("business.hints.account.heading", locale: "de"));

        $this->keyserverKnows();

        $this->signedIn()->get("/konto", self::speaking("en-US,en"))->assertOk()
            ->assertDontSee('id="charge-business"', false);
    }

    /**
     * Und im Beitrittsformular selbst: der Zweig „Als Firma beitreten?“ gab es
     * schon, er erklärte nur nichts. Das Formular ist ohnehin nur auf Deutsch
     * zu haben — auf Englisch antwortet es mit `membership.nonGerman` —, aber
     * geprüft wird beides, weil der Hinweis sonst der erste wäre, der die Regel
     * nicht kennt.
     */
    public function testTheCompanyBranchOfTheFormPointsAtTheBusinessPage(): void
    {
        $this->get("/membership?type=company", self::speaking("de-DE,de"))->assertOk()
            ->assertSee("company-hint", false)
            ->assertSee(route("business"), false);

        $this->get("/membership?type=company", self::speaking("en-US,en"))->assertOk()
            ->assertDontSee("company-hint", false);
    }

    /**
     * Ein Mitglied darf nicht aufladen, und die Begründung dafür verlinkt
     * ebenfalls das Beitrittsformular. Auch dieser Link ist deutsch — ein
     * Mitglied mit englischer Oberfläche bekommt den Satz, aber nicht den
     * Weg in ein Formular, das er nicht lesen kann.
     */
    public function testAMemberIsNotSentToAGermanFormFromAnEnglishPage(): void
    {
        $this->keyserverKnows(membershipEnd: "2027-12-31 00:00:00");

        $this->signedIn()->get("/konto", self::speaking("en-US,en"))
            ->assertOk()
            ->assertSeeText(__("account.page.charge.blocked.member", locale: "en"))
            ->assertDontSee(route("membership_form"), false);
    }
}
