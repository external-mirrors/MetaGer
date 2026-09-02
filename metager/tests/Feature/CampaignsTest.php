<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Gutscheinaktionen — /konto/gutscheinaktionen,
 * App\Http\Controllers\CampaignController.
 *
 * Aus dem `/key/<uuid>/campaigns`-Bereich des Keymanagers hierher gezogen.
 * Anders als bei {@see OrdersTest} gibt es hier keine eigene
 * Zugehörigkeitsprüfung zu testen: die Kampagnen-API liefert keinen
 * Schlüssel im Antwortkörper zurück, wem eine Kampagne gehört, entscheidet
 * ausschließlich der Keyserver anhand von `:key` im Pfad (siehe
 * CampaignController's Klassenkommentar) — ein `:id`, das nicht zu diesem
 * Schlüssel gehört, ist dort eine 404, die hier nur durchgereicht wird.
 */
class CampaignsTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";
    private const ONION = "metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion";

    private function signedIn(): self
    {
        return $this->withUnencryptedCookie("key", self::A_KEY);
    }

    /**
     * @param array<string, \Illuminate\Http\Client\Response|\Closure> $extra
     */
    private function keyserver(array $extra = []): void
    {
        Http::preventStrayRequests();
        Http::fake(array_merge($extra, [
            "*/api/json/key/*" => Http::response([
                "key" => self::A_KEY,
                "charge" => 248,
                "expiration" => "2027-03-14 00:00:00",
                "charge_orders" => [["amount" => 248, "expiration" => "2027-03-14 00:00:00"]],
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]));
    }

    private function campaign(array $overrides = []): array
    {
        return array_merge([
            "id" => 1,
            "name" => "Freundeskreis",
            "active" => true,
            "disabled" => false,
            "tokens_per_key" => 10,
            "total_volume" => 100,
            "backing_expires_at" => "2026-12-01T00:00:00.000Z",
            "public_token" => "abc123XYZ",
            "stats" => [
                "vouchers_total" => 10,
                "vouchers_redeemed" => 2,
                "backing_charge" => 80,
            ],
        ], $overrides);
    }

    public function testThePageRendersTheListAndCreateForm(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns" => Http::response([
                "campaigns" => [$this->campaign()],
                "max_campaign_volume" => 248,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/gutscheinaktionen")
            ->assertOk()
            ->assertSee(trans("campaigns.heading"))
            ->assertSee("Freundeskreis")
            ->assertSee('name="tokens_per_key"', false);
    }

    /**
     * The public link is meant to be pasted somewhere else — it must not
     * carry the visitor's own browsing locale, and it must point at the
     * keymanager's redemption path with the campaign's public token.
     * Regression coverage for exactly the bug `url()` would reintroduce:
     * `AppServiceProvider`'s `URL::formatPathUsing` stamps every `url()`
     * call with the current request's locale prefix.
     */
    public function testThePublicLinkCarriesNoLocalePrefix(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns" => Http::response([
                "campaigns" => [$this->campaign(["public_token" => "TESTTOKEN"])],
                "max_campaign_volume" => 248,
            ]),
        ]);

        $this->signedIn()
            ->get("https://metager.org/pl-PL/konto/gutscheinaktionen")
            ->assertOk()
            ->assertSee("https://metager.org/keys/c/campaign/TESTTOKEN", false)
            ->assertDontSee("/pl-PL/keys/c/campaign", false);
    }

    /**
     * Regression: the link pointed at `config("app.url")`, and `app.url` is
     * not a public address. `config("metager.metager.keymanager.server")`
     * defaults to `app.url . "/keys"`, so `app.url` is where this application
     * reaches the *keyserver* — `http://nginx:8080` in the compose stack, a
     * name that resolves inside the Docker network and nowhere else. Every
     * link the page offered for copying was one nobody could open.
     *
     * The visitor's own origin is what it uses now
     * ({@see \App\Support\AppHosts::shareableOrigin()}), so the assertion is
     * that the configured value is exactly what does *not* appear.
     */
    public function testThePublicLinkIsNotTheInternalKeyserverAddress(): void
    {
        config(["app.url" => "http://nginx:8080"]);

        $this->keyserver([
            "*/api/json/key/*/campaigns" => Http::response([
                "campaigns" => [$this->campaign(["public_token" => "TESTTOKEN"])],
                "max_campaign_volume" => 248,
            ]),
        ]);

        $this->signedIn()
            ->get("https://metager.de/de-DE/konto/gutscheinaktionen")
            ->assertOk()
            ->assertSee("https://metager.de/keys/c/campaign/TESTTOKEN", false)
            ->assertDontSee("nginx:8080");
    }

    /**
     * From an onion address the link deliberately falls back to the canonical
     * host: it is handed to a third party, and an address only Tor can open is
     * worse for them than the clearnet one. Same call the keymanager makes for
     * the printed voucher cards (`redeem_base` in its `routes/api.js`).
     */
    public function testThePublicLinkOffAnOnionAddressUsesTheCanonicalHost(): void
    {
        config(["app.url" => "https://metager.de"]);

        $this->keyserver([
            "*/api/json/key/*/campaigns" => Http::response([
                "campaigns" => [$this->campaign(["public_token" => "TESTTOKEN"])],
                "max_campaign_volume" => 248,
            ]),
        ]);

        $this->signedIn()
            ->get("http://" . self::ONION . "/de-DE/konto/gutscheinaktionen")
            ->assertOk()
            ->assertSee("https://metager.de/keys/c/campaign/TESTTOKEN", false)
            ->assertDontSee(self::ONION . "/keys/c/campaign", false);
    }

    /**
     * Regression: „Dein Schlüssel enthält aktuell 0 Token" über einem vollen
     * Guthaben, und `max="0"` im Formular — womit sich überhaupt keine
     * Kampagne mehr anlegen ließ.
     *
     * `max_campaign_volume` ist `Key.get_non_relay_charge()` drüben, gerechnet
     * als `Math.round(summe * 10) / 10`. Ein Schlüssel, von dem je ein
     * Dezitoken abgebucht wurde, antwortet damit `459.5` — JSON-Bruchzahl,
     * kein `int`, und {@see \App\Authentication\CampaignIssuer::list()} prüfte
     * mit `is_int()`. Das traf jeden benutzten Schlüssel.
     */
    public function testAFractionalBalanceIsTheBudgetAndNotZero(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns" => Http::response([
                "campaigns" => [],
                "max_campaign_volume" => 459.5,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/gutscheinaktionen")
            ->assertOk()
            // Abgerundet: die Anlege-Route drüben validiert `total_volume` mit
            // `isInt()`, und die Kontoseite zeigt Guthaben als `floor($charge)`.
            ->assertSee('max="459"', false)
            ->assertSee(trans("campaigns.create.total_volume_hint", ["charge" => "459"]))
            ->assertDontSee('max="0"', false);
    }

    /**
     * Wenn der Keyserver gar keine Zahl liefert, bleibt es bei 0 — kein
     * geratenes Budget, und der Hinweis darüber sagt ohnehin schon, dass
     * gerade nichts zu laden war.
     */
    public function testANonNumericBudgetStaysZero(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns" => Http::response([
                "campaigns" => [],
                "max_campaign_volume" => null,
            ]),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/gutscheinaktionen")
            ->assertOk()
            ->assertSee('max="0"', false);
    }

    public function testAVisitorWithoutAKeyIsSentToSignIn(): void
    {
        $this->keyserver();

        $this->get("/de-DE/konto/gutscheinaktionen")->assertRedirect();
    }

    public function testAnUnreachableKeyserverShowsAHint(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns" => Http::response([], 500),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/gutscheinaktionen")
            ->assertOk()
            ->assertSee(trans("campaigns.unreachable"));
    }

    public function testCreatingACampaignRedirectsOnSuccess(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns" => Http::response($this->campaign(), 201),
        ]);

        $response = $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/gutscheinaktionen", [
                "name" => "Freundeskreis",
                "tokens_per_key" => "10",
                "total_volume" => "100",
            ]);

        $response->assertRedirect(route("account.campaigns"));

        Http::assertSent(fn ($request) => $request->method() === "POST"
            && str_contains($request->url(), "/campaigns")
            && !str_contains($request->url(), "/disable")
            && $request["name"] === "Freundeskreis"
            && $request["total_volume"] === "100");
    }

    public function testCreatingACampaignShowsTheKeyserversErrorCode(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns" => function ($request) {
                if ($request->method() === "POST") {
                    return Http::response(["code" => 422, "error" => "over_budget"], 422);
                }

                return Http::response(["campaigns" => [], "max_campaign_volume" => 248]);
            },
        ]);

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/gutscheinaktionen", [
                "name" => "Freundeskreis",
                "tokens_per_key" => "10",
                "total_volume" => "10000",
            ])
            ->assertOk()
            ->assertSee(trans("campaigns.create.error.over_budget"));
    }

    public function testCreatingACampaignWithAForeignOriginIsRejected(): void
    {
        $this->keyserver();

        $this->signedIn()
            ->withHeader("Origin", "https://evil.example")
            ->post("/de-DE/konto/gutscheinaktionen", ["name" => "x"])
            ->assertForbidden();
    }

    public function testDisablingACampaignRedirectsBackToTheList(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns/1/disable" => Http::response($this->campaign(["active" => false, "disabled" => true])),
        ]);

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/gutscheinaktionen/1/deaktivieren")
            ->assertRedirect(route("account.campaigns"));
    }

    public function testDisablingACampaignWithAForeignOriginIsRejected(): void
    {
        $this->keyserver();

        $this->signedIn()
            ->withHeader("Origin", "https://evil.example")
            ->post("/de-DE/konto/gutscheinaktionen/1/deaktivieren")
            ->assertForbidden();
    }

    public function testDeletingACampaignRedirectsBackToTheList(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns/1/delete" => Http::response(["ok" => true]),
        ]);

        $this->signedIn()
            ->withHeader("Origin", config("app.url"))
            ->post("/de-DE/konto/gutscheinaktionen/1/loeschen")
            ->assertRedirect(route("account.campaigns"));
    }

    public function testTheCardsPdfIsProxiedThrough(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns/1/cards.pdf*" => Http::response(
                "%PDF-1.7 cards",
                200,
                ["Content-Type" => "application/pdf"],
            ),
        ]);

        $response = $this->signedIn()->get("/de-DE/konto/gutscheinaktionen/1/karten.pdf");

        $response->assertOk();
        $this->assertSame("application/pdf", $response->headers->get("Content-Type"));
        $this->assertStringStartsWith("%PDF-", $response->getContent());
        $response->assertHeader("Content-Disposition", 'inline; filename="campaign-1-cards.pdf"');
    }

    public function testTheCardsPdfIs404WhenTheKeyserverHasNone(): void
    {
        $this->keyserver([
            "*/api/json/key/*/campaigns/1/cards.pdf*" => Http::response(["code" => 404, "error" => "not_found"], 404),
        ]);

        $this->signedIn()
            ->get("/de-DE/konto/gutscheinaktionen/1/karten.pdf")
            ->assertNotFound();
    }
}
