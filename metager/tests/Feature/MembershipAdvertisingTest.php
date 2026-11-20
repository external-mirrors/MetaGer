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
 *
 * Die Ausnahme ist die Aufladekarte auf /konto: Formular und Begrüßungsmail
 * kommen inzwischen übersetzt aus suma-crm, also wird dort in jeder Sprache
 * geworben, mit einem Hinweis, dass Versammlungen und Protokolle deutsch
 * bleiben.
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

    /**
     * Auf der Aufladeseite wird überall geworben, aber nur die deutsche
     * Oberfläche kommt ohne den Hinweis aus, dass Einladungen zur
     * Mitgliederversammlung und ihre Protokolle auf Deutsch kommen.
     */
    public function testOnlyTheGermanInterfaceGoesWithoutTheLanguageNotice(): void
    {
        foreach (["de-DE", "de-AT", "de-CH"] as $locale) {
            LocaleContext::resolve(Request::create("http://metager.de/$locale/"))->apply();
            $this->assertFalse(MembershipOffer::needsLanguageNotice(), "$locale should not need the notice");
        }

        foreach (["en-US", "en-GB", "es-ES", "pl-PL", "nl-NL"] as $locale) {
            LocaleContext::resolve(Request::create("http://metager.de/$locale/"))->apply();
            $this->assertTrue(MembershipOffer::needsLanguageNotice(), "$locale should need the notice");
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

        // Anonyme Token sind die Antwort auf „dann weiß der Verein, wonach ich
        // suche“ — die Hilfeseite dazu ist verlinkt.
        $response->assertSee("/hilfe/anonyme-token", false);

        // Deutsch ist die Vereinssprache, hier gibt es nichts anzumerken.
        $response->assertDontSee("account-membership__language", false);
    }

    /**
     * Die Mitgliedschaft ist der Vorschlag, das Paket die Alternative: sie
     * steht zuerst, die Pakete folgen.
     */
    public function testTheChargeSectionPutsMembershipBeforeThePackages(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/konto", self::speaking("de-DE,de"))
            ->assertOk()
            ->assertSeeInOrder(['id="charge-membership"', 'class="account-tiers"'], false);
    }

    /**
     * Beitrittsformular und Begrüßungsmail gibt es in mehreren Sprachen
     * (suma-crm, über `?lang=`), deshalb wird auf der Aufladeseite in jeder
     * Sprache geworben. Was deutsch bleibt — Einladungen zur
     * Mitgliederversammlung und Protokolle —, sagt der Hinweis.
     */
    public function testTheChargeSectionOffersMembershipInEveryLanguage(): void
    {
        $this->keyserverKnows();

        foreach (["en-US,en" => "en", "pl-PL,pl" => "pl"] as $header => $language) {
            $response = $this->signedIn()->get("/konto", self::speaking($header))->assertOk();

            $response->assertSee('id="charge-membership"', false);
            $response->assertSeeText(__("account.page.charge.membership.heading", locale: $language));
            $response->assertSee("account-membership__language", false);
            $response->assertSeeText(__("account.page.charge.membership.language.heading", locale: $language));
            $response->assertSee("account-tiers", false);
        }
    }

    /**
     * Ein Mitglied darf nicht aufladen und bekommt den Satz, warum. Darunter
     * stand ein Link mit der Beschriftung „Alle Preise und Zahlungswege“, der
     * ins Beitrittsformular führte: weder das eine noch das andere hat ein
     * Mitglied hier zu suchen.
     */
    public function testAMemberIsNotSentAnywhereFromTheChargeSection(): void
    {
        $this->keyserverKnows(membershipEnd: "2027-12-31 00:00:00");

        foreach (["de-DE,de" => "de", "en-US,en" => "en"] as $header => $language) {
            $this->signedIn()->get("/konto", self::speaking($header))
                ->assertOk()
                ->assertSeeText(__("account.page.charge.blocked.member", locale: $language))
                ->assertDontSee("account-section__more", false);
        }

        // Auf Englisch hat auch das Seitenmenü keinen Link ins Formular, also
        // darf die Seite ihn gar nicht enthalten. (Auf Deutsch wirbt das
        // Seitenmenü, dort ist die Adresse ohnehin im Markup.)
        $this->signedIn()->get("/konto", self::speaking("en-US,en"))
            ->assertDontSee(route("membership_form"), false);
    }

    /**
     * Die Karte zeigt die Preise selbst, und die Zahlungswege kommen im
     * nächsten Schritt. Ein Link auf /preise führte nur weg von der Seite, auf
     * der jemand gerade aufladen wollte.
     */
    public function testTheChargeSectionDoesNotSendAnyoneAwayToThePricePage(): void
    {
        $this->keyserverKnows();

        $this->signedIn()->get("/konto", self::speaking("de-DE,de"))
            ->assertOk()
            ->assertSee("account-tiers", false)
            ->assertDontSee("account-section__more", false);
    }
}
