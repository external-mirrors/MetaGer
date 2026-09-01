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
            ->get("/pl-PL/konto/gutscheinaktionen")
            ->assertOk()
            ->assertSee(rtrim(config("app.url"), "/") . "/keys/c/campaign/TESTTOKEN", false)
            ->assertDontSee("/pl-PL/keys/c/campaign", false);
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
