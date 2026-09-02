<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Einen Gutscheincode einlösen — /c, App\Http\Controllers\VoucherController.
 *
 * Aus dem `/c`-Bereich des Keymanagers hierher gezogen, im selben Schnitt wie
 * {@see CampaignsTest}: was drüben blieb, ist die API über die
 * `campaigns`/`campaign_vouchers`-Tabellen ({@see \App\Authentication\
 * CampaignRedemption}); die Codeeingabe, die Vorschau und das Ergebnis stehen
 * hier.
 *
 * Zwei Einlösewege ({@see testCodeTeaserShowsWhatItIsWorth()} und
 * {@see testPublicLinkTeaserShowsWhatItIsWorth()}), beide über dieselben zwei
 * Endpunkte drüben (`/c/voucher/*`, `/c/campaign/*`), und eine geteilte
 * Bremse: fehlgeschlagene Codes zählen pro Adresse, hier — der Keyserver
 * sieht von seiner Seite nur einen angemeldeten Aufrufer.
 */
class VoucherTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";
    private const CODE = "ABCD1234EF";

    protected function setUp(): void
    {
        parent::setUp();

        // Sonst zählt der vorige Test in den nächsten hinein: die Bremse
        // zählt pro Adresse, und in Tests ist das immer dieselbe.
        RateLimiter::clear("voucher:127.0.0.1");
    }

    private function teaserBody(array $overrides = []): array
    {
        return [
            "campaign" => array_merge([
                "name" => "Freundeskreis",
                "tokens_per_key" => 10,
                "relay_expiration_days" => 30,
            ], $overrides),
        ];
    }

    private function redeemedBody(array $overrides = []): array
    {
        return array_merge([
            "key" => self::A_KEY,
            "effective_charge" => 10,
            "expiration" => "2027-03-14T00:00:00.000Z",
            "campaign" => ["name" => "Freundeskreis", "tokens_per_key" => 10],
        ], $overrides);
    }

    public function testCodeTeaserShowsWhatItIsWorth(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/voucher/*" => Http::response($this->teaserBody())]);

        $this->get("/de-DE/c/" . self::CODE)
            ->assertOk()
            ->assertSee("Freundeskreis")
            ->assertSee(trans("campaigns.redeem.teaser.submit"));
    }

    /**
     * Der Status wird durchgereicht, nicht auf 200 geglättet: 404 für einen
     * Code, der nichts ist. Die Seite ist trotzdem unsere eigene — die
     * Fehlerseite dieses Vorgangs, nicht Laravels 404.
     */
    public function testCodeTeaser404sAsError(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/voucher/*" => Http::response(["error" => "invalid_code"], 404)]);

        $this->get("/de-DE/c/" . self::CODE)
            ->assertNotFound()
            ->assertSee(trans("campaigns.redeem.error.heading"))
            ->assertSee(trans("campaigns.redeem.error.invalid_code"));
    }

    /**
     * 410 für einen Code, der einmal etwas war und verbraucht ist — damit ein
     * Crawler die tote Seite aus dem Index nimmt.
     */
    public function testAlreadyRedeemedCodeShowsThatError(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/voucher/*" => Http::response(["error" => "already_redeemed"], 410)]);

        $this->get("/de-DE/c/" . self::CODE)
            ->assertStatus(410)
            ->assertSee(trans("campaigns.redeem.error.already_redeemed"));
    }

    public function testRedeemingACodeSetsTheKeyCookieAndShowsIt(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/voucher/*/redeem" => Http::response($this->redeemedBody())]);

        $this->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/c/" . self::CODE)
            ->assertOk()
            ->assertSee(self::A_KEY)
            // Unverschlüsselt: `key` steht in EncryptCookies::$except, weil
            // der Keymanager es unter demselben Host lesen können muss.
            ->assertCookie("key", self::A_KEY, false);
    }

    public function testRedeemFailsWithoutSameOrigin(): void
    {
        Http::preventStrayRequests();

        $this->withHeaders(["Origin" => "https://evil.example"])
            ->post("/de-DE/c/" . self::CODE)
            ->assertForbidden();
    }

    public function testPublicLinkTeaserShowsWhatItIsWorth(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/campaign/*" => Http::response($this->teaserBody())]);

        $this->get("/de-DE/c/campaign/sometoken")
            ->assertOk()
            ->assertSee("Freundeskreis");
    }

    public function testPublicLinkRedeemSetsTheKeyCookie(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/campaign/*/redeem" => Http::response($this->redeemedBody())]);

        $this->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/c/campaign/sometoken")
            ->assertOk()
            ->assertCookie("key", self::A_KEY, false);
    }

    public function testPublicLinkRateLimitedShowsThatError(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/campaign/*/redeem" => Http::response(["error" => "rate_limited"], 429)]);

        $this->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/c/campaign/sometoken")
            ->assertStatus(429)
            ->assertSee(trans("campaigns.redeem.error.rate_limited"));
    }

    /**
     * Nur fehlgeschlagene Nachschläge zählen — wie beim Keymanager
     * (`campaigns.redeem_rate_limit`) und wie bei der Anmeldung. Geprüft wird
     * direkt an der Bremse und nicht über eine Schleife echter Anfragen: in
     * einer Testmethode trifft nur die *erste* Anfrage die Routen-Tabelle,
     * weil ResolveLocale das Sprachpräfix aus der Anfrage schneidet (siehe
     * LoginSubmitTest::testGuessingIsSlowedDown()).
     */
    public function testOnlyFailedCodeLookupsCountTowardsTheLimit(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/voucher/*" => Http::response($this->teaserBody())]);

        $this->get("/de-DE/c/" . self::CODE)
            ->assertOk()
            ->assertSee("Freundeskreis");

        $this->assertSame(0, RateLimiter::attempts("voucher:127.0.0.1"));
    }

    public function testTooManyFailedLookupsAreRateLimited(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/c/voucher/*" => Http::response(["error" => "invalid_code"], 404)]);

        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit("voucher:127.0.0.1", 3600);
        }

        $this->get("/de-DE/c/" . self::CODE)
            ->assertStatus(429)
            ->assertSee(trans("campaigns.redeem.error.rate_limited"));
    }

    /**
     * Die Codeeingabe normalisiert selbst — Kleinbuchstaben, Bindestriche,
     * mehrdeutige Zeichen — und schickt erst danach auf die Vorschauseite.
     * Wortgleich zu `CampaignVoucher.NORMALIZE_CODE` im Keymanager.
     */
    public function testTheEntryFormNormalizesTheCodeBeforeRedirecting(): void
    {
        $this->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/c", ["code" => "abcd-1234-el"])
            ->assertRedirectContains("/de-DE/c/ABCD1234E1");
    }

    public function testTheEntryFormRejectsAnInvalidCode(): void
    {
        $this->withHeaders(["Origin" => config("app.url")])
            ->post("/de-DE/c", ["code" => "too-short"])
            ->assertStatus(422)
            ->assertSee(trans("campaigns.redeem.enter.invalid_code"));
    }

    public function testAKeyHolderIsSentToTheirAccountInsteadOfTheEntryForm(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*/api/json/key/*" => Http::response(["key" => self::A_KEY, "charge" => 0])]);

        $this->withUnencryptedCookie("key", self::A_KEY)
            ->get("/de-DE/c")
            ->assertRedirectContains("/de-DE/konto");
    }
}
