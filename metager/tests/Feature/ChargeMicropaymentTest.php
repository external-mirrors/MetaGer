<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Micropayment — /konto/aufladen/<menge>/micropayment(/<service>).
 *
 * Der erste Bezahlvorgang hier, der die Zahlung selbst nicht mehr lokal
 * abwickelt: `micropaymentSubmit` legt die Ladung an
 * (App\Authentication\MicropaymentChargeIssuer, dessen Gegenstück
 * pass/routes/api.js's neuer /key/:key/checkout/micropayment/:service-
 * Endpunkt) und leitet dann auf eine fremde, bereits mit dem Anbieter-Siegel
 * versehene Adresse weiter — kein `assertRedirect` gegen eine eigene Route,
 * sondern gegen genau das, was der Keyserver zurückgegeben hat.
 */
class ChargeMicropaymentTest extends TestCase
{
    private const A_KEY = "5e9c1a2b-4f6d-4c3e-9a71-2b8d0f4e6c15";

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear("account-logincode:" . self::A_KEY);
    }

    private function keyserverKnows(array $extraFakes = []): void
    {
        Http::preventStrayRequests();
        Http::fake(array_merge($extraFakes, [
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
                "key_config" => ["membershipEndDate" => null],
            ]),
        ]));
    }

    private function signedIn(): self
    {
        return $this->withUnencryptedCookie("key", self::A_KEY);
    }

    private function submit(string $service, array $fields, array $headers = []): \Illuminate\Testing\TestResponse
    {
        return $this->signedIn()
            ->withHeaders(array_merge(["Origin" => config("app.url")], $headers))
            ->post("/de-DE/konto/aufladen/1000/micropayment/{$service}", $fields);
    }

    /**
     * Es gibt keine eigene Micropayment-Wahl-Seite mehr — die drei Zahlweisen
     * sind Kacheln auf dem allgemeinen Zahlungsarten-Chooser, wie jede andere
     * Zahlweise auch (Feedback: "Micropayment" allein sagt niemandem, der den
     * Anbieter nicht kennt, was sich dahinter verbirgt).
     */
    public function testTheChooserLinksToAllThreeServices(): void
    {
        $this->keyserverKnows();

        $response = $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000")
            ->assertOk();

        foreach (["prepay", "lastschrift", "directbanking"] as $service) {
            $response->assertSee(
                route("account.checkout.micropayment.service", ["amount" => 1000, "service" => $service]),
                false
            );
        }
    }

    public function testTheServiceFormPostsToItself(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/micropayment/prepay")
            ->assertOk()
            ->assertSee(route("account.checkout.micropayment.submit", ["amount" => 1000, "service" => "prepay"]), false);
    }

    /**
     * Die Zustimmungsseite trägt eine eigene CSP, deren `form-action` den
     * micropayment-Host einschließt. Ohne sie gilt die nginx-Standard-CSP
     * (`form-action 'self' metager.org metager.de`), und Chrome/Safari
     * blockieren dann die 303-Weiterleitung des Formulars auf die gesiegelte
     * Zahlungsseite — der Bug, wegen dessen SEPA-Lastschrift und
     * Sofortüberweisung im Konto ins Leere liefen.
     *
     * Geprüft wird der Vertrag des Controllers, nicht der Browser: die
     * nginx-Standard-CSP, die ohne diesen Header gälte, ist aus einem
     * PHP-Test heraus nicht sichtbar, und Firefox (Dusk) würde den Fehler
     * ohnehin nicht zeigen.
     */
    public function testTheServicePageWidensFormActionForTheRedirectToMicropayment(): void
    {
        $this->keyserverKnows();

        // Eine Zahlweise genügt: der `->header()`-Aufruf in
        // micropaymentServiceShow hängt nicht am Service. Mehrere
        // $this->get() in einem Testkörper gehen nicht — ResolveLocale
        // streift das `/de-DE` nur beim ersten ab (siehe CLAUDE.md).
        $csp = $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/micropayment/lastschrift")
            ->assertOk()
            ->headers->get("Content-Security-Policy");

        $this->assertNotNull($csp);
        $this->assertMatchesRegularExpression('/form-action[^;]*\bmicropayment\.de\b/', $csp);
        // Der Header ersetzt die ganze Policy — die übrigen Direktiven der
        // nginx-Vorgabe müssen mitkommen, sonst bricht auf dieser Seite
        // etwas Unbeteiligtes.
        $this->assertStringContainsString("worker-src 'self' blob:", $csp);
        $this->assertStringContainsString("scripts.zdv.uni-mainz.de", $csp);
    }

    public function testPrepayOffersTheOptionalEmailField(): void
    {
        $this->keyserverKnows();

        // Nicht required - wer ohne Konto und ohne Mailadresse zahlen will,
        // soll das können, derselbe Grund wie bei der späteren Kreditkarten-
        // Zahlart.
        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/micropayment/prepay")
            ->assertOk()
            // Kein `required`: das ist die Aussage dieses Tests. Nicht mehr
            // das ganze Tag wörtlich — es trägt seit der Gestaltungsrunde ein
            // aria-describedby auf den Hinweis darunter, und ein Test, der an
            // der Attributliste hängt, wäre bei jeder solchen Ergänzung rot,
            // ohne dass sich das Geprüfte geändert hätte.
            ->assertSee('name="email" id="checkout-micropayment-email"', false)
            ->assertDontSee('name="email" id="checkout-micropayment-email" required', false);
    }

    public function testLastschriftHasNoEmailField(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/micropayment/lastschrift")
            ->assertOk()
            ->assertDontSee('name="email"', false);
    }

    public function testSubmittingWithoutConsentIsRefused(): void
    {
        $this->keyserverKnows();

        $this->submit("prepay", [])
            ->assertRedirect(route("account.checkout.micropayment.service", ["amount" => 1000, "service" => "prepay"]) . "?error=consent");

        Http::assertNotSent(fn ($request) => str_contains($request->url(), "/checkout/micropayment"));
    }

    public function testConsentingCreatesTheOrderAndLeavesForTheProvider(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/micropayment/*" => Http::response([
                "public_id" => "Z1",
                "redirect_url" => "https://prepayment.micropayment.de/prepay/event?seal=abc123",
            ], 201),
        ]);

        $this->submit("prepay", ["revocation" => "on"])
            ->assertRedirect("https://prepayment.micropayment.de/prepay/event?seal=abc123");
    }

    /**
     * The browser-facing origin travels to the keymanager as `return_origin`,
     * which micropayment's own async webhook builds the "back to MetaGer"
     * redirect from — see App\Support\AppHosts and ChargeVRPaymentTest for why
     * the keymanager cannot work this out on its own.
     */
    public function testTheBrowserOriginIsForwardedAsReturnOrigin(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/micropayment/*" => Http::response([
                "public_id" => "Z1",
                "redirect_url" => "https://prepayment.micropayment.de/prepay/event?seal=abc123",
            ], 201),
        ]);

        $this->signedIn()
            ->withHeaders(["Origin" => "https://metager.org"])
            ->post("https://metager.org/de-DE/konto/aufladen/1000/micropayment/prepay", ["revocation" => "on"]);

        Http::assertSent(fn ($request) => str_contains($request->url(), "/checkout/micropayment/prepay")
            && $request["return_origin"] === "https://metager.org");
    }

    public function testTheOptionalEmailIsForwarded(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/micropayment/*" => Http::response([
                "public_id" => "Z1",
                "redirect_url" => "https://prepayment.micropayment.de/prepay/event?seal=abc123",
            ], 201),
        ]);

        $this->submit("prepay", ["revocation" => "on", "email" => "max@example.com"]);

        Http::assertSent(fn ($request) => str_contains($request->url(), "/checkout/micropayment/prepay")
            && $request["email"] === "max@example.com");
    }

    public function testAnUnreachableKeyserverBouncesBackWithAnError(): void
    {
        $this->keyserverKnows([
            "*/api/json/key/*/checkout/micropayment/*" => Http::response(null, 500),
        ]);

        $this->submit("prepay", ["revocation" => "on"])
            ->assertRedirect(route("account.checkout.micropayment.service", ["amount" => 1000, "service" => "prepay"]) . "?error=unreachable");
    }

    public function testAForeignFormIsRefused(): void
    {
        $this->keyserverKnows();

        $this->submit("prepay", ["revocation" => "on"], ["Origin" => "https://evil.example"])
            ->assertForbidden();
    }

    public function testAnUnknownServiceBouncesBackToTheChooser(): void
    {
        $this->keyserverKnows();

        $this->signedIn()
            ->get("/de-DE/konto/aufladen/1000/micropayment/not-a-service")
            ->assertRedirect(route("account.checkout", ["amount" => 1000]));
    }
}
