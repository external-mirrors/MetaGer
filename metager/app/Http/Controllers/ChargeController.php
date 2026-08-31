<?php

namespace App\Http\Controllers;

use App\Authentication\ChargeOrderIssuer;
use App\Authentication\KeyIssuer;
use App\Authentication\KeyUser;
use App\Authentication\ManualChargeIssuer;
use App\Authentication\MicropaymentChargeIssuer;
use App\Authentication\PayPalChargeIssuer;
use App\Authentication\VRPaymentChargeIssuer;
use App\Landing\ChargeEligibility;
use App\Landing\KeymanagerLinks;
use App\Landing\KeyPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;

/**
 * Aufladen — /konto/aufladen/<menge>, ohne Schlüssel im Pfad.
 *
 * Der zweite Schritt des Bezahlvorgangs, der aus dem Keymanager hierher
 * zieht; {@see AccountController} bleibt der erste (die Wahl des Pakets). Wie
 * bei jedem Schritt davor steht der Schlüssel in keiner Adresse — er kommt
 * aus dem Cookie, über {@see \App\Authentication\KeyAuthGuard}.
 *
 * **Jede Zahlart läuft inzwischen hier** — Bar, micropayment, VR Payment, die
 * Entwicklungs-Zahlart, und zuletzt PayPal.
 *
 * **`show()` verlinkt jede einzelne Zahlweise direkt, flach, keine
 * Zwischen-Wahl-Seite pro Anbieter.** Micropayment (`prepay`, `lastschrift`,
 * `directbanking`) und PayPal (sieben Zahlweisen) hatten je eine eigene
 * Wahl-Seite, auf der zuerst der Anbieter stand und erst danach die
 * eigentliche Zahlweise — Feedback dazu: wer bezahlen will, sucht eine
 * Zahlweise, die er kennt ("Kreditkarte", "Lastschrift"), keinen Anbieter,
 * den er nicht kennt ("Micropayment"). `show()` listet darum alle elf
 * Zahlweisen selbst, sortiert nach Datenschutzfreundlichkeit: Bar (anonym),
 * Wero, die drei Micropayment-Zahlweisen, dann die sieben PayPal-Zahlweisen.
 * `account.checkout.micropayment` und `account.checkout.paypal` (ohne
 * `service`/`fundingSource`) gibt es deshalb nicht mehr — jede Weiterleitung,
 * die früher dorthin zurückführte (ungültige Auswahl, nicht erreichbarer
 * Keyserver, "zurück"), führt jetzt zu `show()` selbst, mit `?error=` für den
 * Fall, dass etwas mitzuteilen ist.
 *
 * **Bar braucht zwei Seiten statt einer.** `cashShow`/`cashSubmit` legen den
 * Auftrag an; `cashCreated` zeigt ihn. Dazwischen steht eine Weiterleitung
 * (POST/redirect/GET), keine erneut gerenderte POST-Antwort — die alte
 * Kasse im Keymanager rendert nach dem Anlegen dieselbe Adresse noch einmal,
 * und ein Neuladen legt dort einen zweiten Auftrag an. Ohne Sitzung trägt die
 * Weiterleitung nichts als die öffentliche Auftragsnummer; `cashCreated`
 * fragt die Ladung darüber noch einmal beim Keyserver nach, statt etwas zu
 * glauben, das nur durch den Redirect mitgereist wäre, und prüft dabei, dass
 * die Ladung wirklich dem angemeldeten Schlüssel gehört — die Nummer ist
 * klein und fortlaufend, kein Geheimnis.
 *
 * **Micropayment verlässt MetaGer für die Zahlung selbst.** `micropayment
 * ServiceShow` rendert die Zustimmungsseite einer der drei Zahlweisen direkt
 * (wie `cashShow`); `micropaymentSubmit` legt den Auftrag an und leitet
 * direkt auf die von drüben gelieferte, bereits mit dem Anbieter-Siegel
 * versehene Zahlungsseite weiter (303, kein lokales Ziel). Der Rückweg von
 * dort landet nicht wieder bei MetaGer im laufenden Vorgang, sondern auf
 * {@see returned()} — derselben Landeseite, die VR Payment ebenfalls nutzt.
 * Kein Beleg, nur "bezahlt" oder "wird noch bearbeitet", anhand des
 * `paid`-Felds, das {@see ChargeOrderIssuer::find()} mitbringt.
 *
 * **VR Payment (Wero) verlässt MetaGer ebenso.** Nur eine Zahlart
 * ({@see vrpaymentShow()} rendert die Zustimmungsseite direkt, wie
 * `cashShow`); `vrpaymentSubmit` legt den Auftrag an und leitet genauso
 * weiter wie micropayment. Der Rückweg landet, ohne dass diese Klasse etwas
 * dafür tun muss, auf derselben {@see returned()}, die micropayment schon
 * nutzt — dafür wurde sie generisch gebaut. Die Kachel auf `show()` heißt
 * "Wero", nicht "VR Payment" — VR Payment ist der Anbieter dahinter, Wero die
 * einzige Zahlweise, die er hier anbietet, und die Zahlweise ist es, die auf
 * der Kachel stehen soll.
 *
 * **Wer zahlt, steht auf jeder Seite hier** — partials/key-fingerprint.blade.php,
 * dasselbe Kürzel wie auf /konto. "Zugang sichern" (partials/key-backup.blade.php)
 * dagegen bewusst nicht: dieser Vorgang ist eine Entscheidung, kein Ort zum
 * Verwalten, und die Sicherung bleibt auf /konto, wo sie hingehört.
 *
 * **PayPal ist SDK-getrieben, nicht formularbasiert.** Sieben Zahlweisen,
 * jede eine eigene Kachel auf `show()`; `paypalServiceShow` holt vor dem
 * Rendern die Konfiguration (Client-ID, ob Kartenzahlung gerade erlaubt ist)
 * drüben ab — der einzige Seitenaufruf in dieser Klasse, der selbst schon
 * zum Keyserver spricht — und setzt eine Content-Security-Policy fürs
 * PayPal-SDK. `paypalOrderCreate`/`paypalOrderCapture` sind JSON-Ziele, die
 * resources/js/checkout-paypal.js per fetch aufruft (das SDK ruft
 * `createOrder`/`onApprove` auf, nicht ein Formular-Submit) — nie mit dem
 * Bearer-Token, das diese Klasse für {@see \App\Authentication\
 * PayPalChargeIssuer} hält; das bleibt serverseitig. Ohne Javascript bietet
 * `show()` die sieben PayPal-Kacheln gar nicht erst an (dieselbe
 * `hidden`-Vorlage wie `#login-qr`), statt Seiten zu zeigen, die nichts tun.
 *
 * **Drei Wege zurück, nicht einer.** "Menge ändern" (jede Seite) und "andere
 * Zahlungsart" (jede Zahlweisen-Seite führt zurück zu `show()`) bleiben im
 * Vorgang; "Zurück zum Konto" (`cancelUrl`, jede Seite) verlässt ihn ganz —
 * bewusst ohne `#charge`-Anker, weil das hier "ich will gar nicht (mehr)
 * aufladen" heißt und nicht "ich will ein anderes Paket".
 */
final class ChargeController extends Controller
{
    public function show(Request $request, int $amount): Response|RedirectResponse
    {
        [$user, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        $tiers = KeyPrice::tiers();
        if (!array_key_exists($amount, $tiers)) {
            return redirect()->to(route("account") . "#charge");
        }

        $blocked = ChargeEligibility::blockedReason($request, $user, $user->getChargeOrders());
        if ($blocked !== null) {
            return redirect()->to(route("account") . "#charge");
        }

        // Kommt von einer Zahlweisen-Seite zurück, deren Auswahl ungültig war
        // oder deren Keyserver nicht antwortete (micropaymentServiceShow,
        // paypalServiceShow), oder vom SDK, wenn PayPal eine Zahlweise beim
        // Laden als hier nicht anbietbar meldet (resources/js/checkout-
        // paypal.js). Es gibt keine Zwischen-Wahl-Seite pro Anbieter mehr, zu
        // der das früher zurückführte — diese Seite ist jetzt das einzige
        // "zurück".
        $error = $request->query("error");
        $error = in_array($error, ["unreachable", "funding_source_not_eligible"], true) ? $error : null;

        return $this->render("checkout.index", $request, $key, $amount, [
            "price" => $tiers[$amount],
            "error" => $error,
        ]);
    }

    public function cashShow(Request $request, int $amount): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        // Die zwei Fehler, die cashSubmit hierher zurückschickt — der
        // Keyserver hat nicht geantwortet, oder die Zustimmung fehlte. Wie
        // bei KeyCreationController::ERRORS steht die Liste dafür extra hier,
        // statt jede Zeichenkette aus der Query ungeprüft in eine Vorlage zu
        // reichen.
        $error = $request->query("error");
        $error = in_array($error, ["unreachable", "consent"], true) ? $error : null;

        return $this->render("checkout.cash", $request, $key, $amount, [
            "reference" => null,
            "error" => $error,
        ]);
    }

    public function cashSubmit(Request $request, int $amount, ChargeOrderIssuer $issuer): RedirectResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [$user, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        if (
            !array_key_exists($amount, KeyPrice::tiers())
            || ChargeEligibility::blockedReason($request, $user, $user->getChargeOrders()) !== null
        ) {
            return redirect()->to(route("account") . "#charge");
        }

        if (!$request->boolean("revocation")) {
            return redirect()
                ->to(route("account.checkout.cash", ["amount" => $amount]) . "?error=consent", 303)
                ->header("Cache-Control", "no-store, private");
        }

        $order = $issuer->create($key, $amount);

        // 303: das Ziel ist eine Seite, die der Browser mit GET holen soll,
        // statt den Auftrag bei einem Neuladen zu wiederholen.
        if ($order === null) {
            return redirect()
                ->to(route("account.checkout.cash", ["amount" => $amount]) . "?error=unreachable", 303)
                ->header("Cache-Control", "no-store, private");
        }

        return redirect()
            ->to(route("account.checkout.cash.created", [
                "amount" => $amount,
                "reference" => $order["public_id"],
            ]), 303)
            ->header("Cache-Control", "no-store, private");
    }

    public function cashCreated(Request $request, int $amount, string $reference, ChargeOrderIssuer $issuer): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        $order = $issuer->find($reference);

        // Weder eine fremde Ladung noch eine, die es nicht gibt, ist etwas,
        // das diese Seite anzeigen darf — die Nummer ist zwar kein Geheimnis,
        // aber wessen Auftrag es ist, entscheidet sich hier und nicht drüben.
        if ($order === null || $order["key"] !== $key) {
            abort(404);
        }

        return $this->render("checkout.cash", $request, $key, $amount, [
            "reference" => [
                "public_id" => $order["public_id"],
                "expiration" => Carbon::parse($order["expires_at"]),
            ],
        ]);
    }

    public function manualShow(Request $request, int $amount): Response|RedirectResponse
    {
        if (!app()->environment("local")) {
            abort(404);
        }

        [, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        return $this->render("checkout.manual", $request, $key, $amount, []);
    }

    public function manualSubmit(Request $request, int $amount, ManualChargeIssuer $issuer): RedirectResponse
    {
        if (!app()->environment("local")) {
            abort(404);
        }

        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [$user, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        if (
            !array_key_exists($amount, KeyPrice::tiers())
            || ChargeEligibility::blockedReason($request, $user, $user->getChargeOrders()) !== null
        ) {
            return redirect()->to(route("account") . "#charge");
        }

        $issuer->charge($key, $amount);

        return redirect()
            ->to(route("account") . "#charge")
            ->header("Cache-Control", "no-store, private");
    }

    public function micropaymentServiceShow(Request $request, int $amount, string $service): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        if (!in_array($service, MicropaymentChargeIssuer::SERVICES, true)) {
            return redirect()->to(route("account.checkout", ["amount" => $amount]));
        }

        $error = $request->query("error");
        $error = in_array($error, ["unreachable", "consent"], true) ? $error : null;

        return $this->render("checkout.micropayment", $request, $key, $amount, [
            "service" => $service,
            "error" => $error,
            "privacyUrl" => MicropaymentChargeIssuer::PRIVACY_URLS[$service],
        ]);
    }

    public function micropaymentSubmit(Request $request, int $amount, string $service, MicropaymentChargeIssuer $issuer): RedirectResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [$user, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        if (!in_array($service, MicropaymentChargeIssuer::SERVICES, true)) {
            return redirect()->to(route("account.checkout", ["amount" => $amount]));
        }

        if (
            !array_key_exists($amount, KeyPrice::tiers())
            || ChargeEligibility::blockedReason($request, $user, $user->getChargeOrders()) !== null
        ) {
            return redirect()->to(route("account") . "#charge");
        }

        if (!$request->boolean("revocation")) {
            return redirect()
                ->to(route("account.checkout.micropayment.service", ["amount" => $amount, "service" => $service]) . "?error=consent", 303)
                ->header("Cache-Control", "no-store, private");
        }

        $email = $request->input("email");
        $order = $issuer->create($key, $amount, $service, is_string($email) && $email !== "" ? $email : null);

        if ($order === null) {
            return redirect()
                ->to(route("account.checkout.micropayment.service", ["amount" => $amount, "service" => $service]) . "?error=unreachable", 303)
                ->header("Cache-Control", "no-store, private");
        }

        // Ein fremder Host, kein lokales Ziel — die Zahlung selbst findet bei
        // micropayment statt, nicht bei uns.
        return redirect()
            ->away($order["redirect_url"], 303)
            ->header("Cache-Control", "no-store, private");
    }

    public function vrpaymentShow(Request $request, int $amount): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        // "failed" kommt von VR Payment selbst zurück (failedUrl), nicht von
        // dieser Anwendung — anders als "unreachable"/"consent" ist es nicht
        // "wir konnten nicht", sondern "die Zahlung wurde abgelehnt".
        $error = $request->query("error");
        $error = in_array($error, ["unreachable", "consent", "vrpayment_failed"], true) ? $error : null;

        return $this->render("checkout.vrpayment", $request, $key, $amount, [
            "error" => $error,
            "privacyUrl" => VRPaymentChargeIssuer::PRIVACY_URL,
        ]);
    }

    public function vrpaymentSubmit(Request $request, int $amount, VRPaymentChargeIssuer $issuer): RedirectResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [$user, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        if (
            !array_key_exists($amount, KeyPrice::tiers())
            || ChargeEligibility::blockedReason($request, $user, $user->getChargeOrders()) !== null
        ) {
            return redirect()->to(route("account") . "#charge");
        }

        if (!$request->boolean("revocation")) {
            return redirect()
                ->to(route("account.checkout.vrpayment", ["amount" => $amount]) . "?error=consent", 303)
                ->header("Cache-Control", "no-store, private");
        }

        $order = $issuer->create($key, $amount);

        if ($order === null) {
            return redirect()
                ->to(route("account.checkout.vrpayment", ["amount" => $amount]) . "?error=unreachable", 303)
                ->header("Cache-Control", "no-store, private");
        }

        // Ein fremder Host, kein lokales Ziel — die Zahlung selbst findet bei
        // VR Payment statt, nicht bei uns.
        return redirect()
            ->away($order["redirect_url"], 303)
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Rendert die Zustimmungs-/SDK-Seite für eine PayPal-Zahlweise.
     *
     * Anders als jede andere Seite in dieser Klasse spricht diese vorm
     * Rendern selbst schon zum Keyserver — {@see PayPalChargeIssuer::show()}
     * holt Client-ID und (nur für "card") ob Kartenzahlung gerade erlaubt
     * ist samt Client-Token. Ohne Antwort landet der Besucher auf `show()`
     * mit `?error=unreachable`, statt eine Seite zu sehen, deren
     * SDK-Bausteine nie funktionieren.
     */
    public function paypalServiceShow(Request $request, int $amount, string $fundingSource): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        if (!in_array($fundingSource, PayPalChargeIssuer::FUNDING_SOURCES, true)) {
            return redirect()->to(route("account.checkout", ["amount" => $amount]));
        }

        $config = (new PayPalChargeIssuer())->show($request, $key, $fundingSource);
        if ($config === null) {
            return redirect()
                ->to(route("account.checkout", ["amount" => $amount]) . "?error=unreachable", 303)
                ->header("Cache-Control", "no-store, private");
        }

        // Anders als bei VR Payment gibt es kein "die Zahlung wurde
        // abgelehnt" über eine Weiterleitung hierher — PayPal verlässt diese
        // Seite nie, ein abgelehnter Versuch zeigt sich als Inline-Meldung
        // im SDK selbst (resources/js/checkout-paypal.js). Stellt das SDK
        // beim Laden fest, dass diese Zahlweise hier gar nicht angeboten
        // wird, geht es zurück zu show() (?error=funding_source_not_eligible
        // dort, nicht hier).
        $error = $request->query("error");
        $error = in_array($error, ["unreachable", "consent"], true) ? $error : null;

        $nonce = Str::random(16);
        // script-src/img-src/form-action nach dem Vorbild von
        // DonationController::paymentMethod() — dort mit `time()` als Nonce,
        // was hier bewusst nicht übernommen ist. connect-src/frame-src sind
        // gegenüber der Spende-CSP erweitert: Advanced Card Fields (die
        // "card"-Zahlweise) braucht beides für seine eigenen Hintergrund-
        // aufrufe und eingebetteten Iframes.
        //
        // c.paypal.com, www.paypalobjects.com (als script-, nicht nur
        // img-src) und cors.api.sandbox.paypal.com/cors.api.paypal.com sind
        // keine Vermutung — das eigentliche, von www.paypal.com/sdk/js
        // ausgelieferte Bundle referenziert alle drei wörtlich:
        // "https://c.paypal.com/da/r/fb.js" (Fraudnet, das PayPals eigene
        // Betrugserkennung speist — ohne dieses Skript sieht PayPal weniger
        // Signal für eine Transaktion, kein harter Fehler, aber ein
        // stillschweigender Qualitätsverlust), ".../upstream/bizcomponents/
        // js/modal.js" (ein echtes Skript, nicht nur die Logos/Icons, für
        // die paypalobjects.com schon in img-src stand) und
        // "cors.api.sandbox.paypal.com" (der Token-Endpunkt, den das SDK
        // ruft, wenn die aufrufende Seite nicht selbst auf paypal.com liegt
        // — unser Fall). cors.api.paypal.com ergänzt dieselbe Umgehung für
        // den Produktivmodus, ungeprüft, weil ihn nur ein Produktiv-Client-
        // ID auslöst.
        $csp = "default-src 'self'; "
            . "script-src 'self' 'nonce-$nonce' https://www.paypal.com https://www.paypalobjects.com https://c.paypal.com; "
            . "script-src-elem 'self' 'nonce-$nonce' https://www.paypal.com https://www.paypalobjects.com https://c.paypal.com; "
            . "style-src 'self' 'unsafe-inline'; "
            . "img-src 'self' www.paypalobjects.com data:; "
            . "font-src 'self'; "
            . "connect-src 'self' https://www.paypal.com https://www.sandbox.paypal.com https://c.paypal.com "
            . "https://cors.api.paypal.com https://cors.api.sandbox.paypal.com; "
            . "frame-src 'self' https://www.paypal.com https://www.sandbox.paypal.com; "
            . "frame-ancestors 'self'; "
            . "form-action 'self' www.paypal.com";

        return $this->render("checkout.paypal", $request, $key, $amount, [
            "fundingSource" => $fundingSource,
            "clientId" => $config["client_id"],
            "directCardEnabled" => $config["direct_card_enabled"],
            "clientToken" => $config["client_token"],
            "nonce" => $nonce,
            "error" => $error,
            "privacyUrl" => "https://www.paypal.com/us/legalhub/privacy-full",
            // Zusätzlich zum gemeinsamen account.js dieser Klasse — das SDK
            // selbst lädt checkout-paypal.js, nicht umgekehrt, deshalb steht
            // es hier und nicht im gemeinsamen render()-Rahmen, der jede
            // Seite dieser Klasse bekommt.
            "js" => [Vite::asset("resources/js/account.js"), Vite::asset("resources/js/checkout-paypal.js")],
        ])->header("Content-Security-Policy", $csp);
    }

    /**
     * JSON-Ziel für resources/js/checkout-paypal.js's `createOrder`-Callback
     * — legt den Auftrag an und gibt die PayPal-Bestellnummer zurück, die
     * das SDK direkt weiterreicht.
     */
    public function paypalOrderCreate(Request $request, int $amount, string $fundingSource, PayPalChargeIssuer $issuer): JsonResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [$user, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return response()->json(["error" => "unauthenticated"], 401);
        }

        if (!in_array($fundingSource, PayPalChargeIssuer::FUNDING_SOURCES, true)) {
            return response()->json(["error" => "invalid_funding_source"], 400);
        }

        if (
            !array_key_exists($amount, KeyPrice::tiers())
            || ChargeEligibility::blockedReason($request, $user, $user->getChargeOrders()) !== null
        ) {
            return response()->json(["error" => "not_eligible"], 409);
        }

        $order = $issuer->createOrder($request, $key, $amount, $fundingSource);
        if ($order === null) {
            return response()->json(["error" => "unreachable"], 502);
        }

        return response()->json([
            "payment_reference" => $order["public_id"],
            "paypal_order_id" => $order["paypal_order_id"],
        ], 201);
    }

    /**
     * JSON-Ziel für resources/js/checkout-paypal.js's `onApprove`-Callback
     * — löst den Auftrag ein. Die Antwort wird nahezu unverändert
     * durchgereicht: das Skript kennt PayPals eigene Fehlerform bereits
     * (Kartenfehlercodes, die 3-D-Secure-Kennung), eine hier vereinfachte
     * Form wäre für das Skript unbrauchbar.
     */
    public function paypalOrderCapture(Request $request, int $amount, string $fundingSource, PayPalChargeIssuer $issuer): JsonResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return response()->json(["errors" => [["msg" => "unauthenticated"]]], 401);
        }

        if (!in_array($fundingSource, PayPalChargeIssuer::FUNDING_SOURCES, true)) {
            return response()->json(["error" => "invalid_funding_source"], 400);
        }

        $paymentReference = $request->input("payment_reference");
        if (!is_string($paymentReference) || !preg_match('/^(Z)?(\d+)$/', $paymentReference)) {
            return response()->json(["errors" => [["msg" => "invalid_payment_reference"]]], 400);
        }

        $result = $issuer->captureOrder($request, $key, $fundingSource, $paymentReference);

        return response()->json($result["body"], $result["status"]);
    }

    public function returned(Request $request, string $reference, ChargeOrderIssuer $issuer): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->requireKeyForReturn($request, $reference);
        if ($redirect !== null) {
            return $redirect;
        }

        $order = $issuer->find($reference);

        if ($order === null || $order["key"] !== $key) {
            abort(404);
        }

        /** @var KeyUser $user */
        $user = Auth::guard("key")->user();

        return response()
            ->view("checkout.returned", [
                "title" => trans("titles.checkout"),
                "navbarFocus" => "login",
                "css" => [Vite::asset("resources/less/metager/pages/account.less")],
                "fingerprint" => $user->getKeyFingerprint(),
                "amount" => $order["amount"],
                "paid" => $order["paid"],
                "accountUrl" => route("account"),
            ])
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Gemeinsamer Rahmen für die drei gerenderten Seiten dieses Vorgangs:
     * Titel, Assets, wer zahlt, und der Weg zurück zur Paketwahl.
     *
     * @param array<string, mixed> $extra
     */
    private function render(string $view, Request $request, string $key, int $amount, array $extra): Response
    {
        /** @var KeyUser $user */
        $user = Auth::guard("key")->user();

        return response()
            ->view($view, array_merge([
                "title" => trans("titles.checkout"),
                "navbarFocus" => "login",
                // account.less trägt die Klassen der geteilten Bausteine
                // (partials/key-fingerprint, partials/key-backup); checkout.less
                // ergänzt nur, was auf dieser Seite neu ist.
                "css" => [
                    Vite::asset("resources/less/metager/pages/account.less"),
                    Vite::asset("resources/less/metager/pages/checkout.less"),
                ],
                "js" => [Vite::asset("resources/js/account.js")],

                "key" => $key,
                "amount" => $amount,
                "fingerprint" => $user->getKeyFingerprint(),
                "changeAmountUrl" => route("account") . "#charge",
                // Der Ausstieg — bewusst ohne #charge: das ist "ich will gar
                // nicht (mehr) aufladen", nicht "ich will ein anderes Paket".
                "cancelUrl" => route("account"),
            ], $extra))
            // Wie /konto: eine Seite mit einer Ladung darauf gehört in
            // keinen Cache, weder in einen gemeinsamen noch in den des
            // Browsers.
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Meldet den Besucher an, oder liefert eine Weiterleitung, die an ihrer
     * statt zurückgegeben werden soll.
     *
     * Dieselbe Reihenfolge wie {@see AccountController::show()}: kein
     * Schlüssel geht zur Anmeldung, ein anonymes Token zur Erklärungsseite
     * der Erweiterung, und ein Schlüssel, den der Keyserver gerade nicht
     * kanonisch beantworten kann, zurück zum Konto — hier gibt es ohne einen
     * verlässlichen Schlüssel nichts aufzuladen.
     *
     * @return array{0: KeyUser, 1: string, 2: null}|array{0: null, 1: null, 2: RedirectResponse}
     */
    private function requireKey(Request $request, int $amount): array
    {
        /** @var KeyUser|null $user */
        $user = Auth::guard("key")->user();

        if ($user === null) {
            return [null, null, redirect()
                ->to(KeymanagerLinks::login(route("account.checkout", ["amount" => $amount]), $request))
                ->header("Cache-Control", "no-store, private")];
        }

        if ($user->temporary) {
            return [null, null, redirect()
                ->to(route("anonymous-token"))
                ->header("Cache-Control", "no-store, private")];
        }

        $key = $this->keyOf($user);
        if ($key === null) {
            return [null, null, redirect()
                ->to(route("account"))
                ->header("Cache-Control", "no-store, private")];
        }

        return [$user, $key, null];
    }

    /**
     * Wortgleich zu {@see requireKey()}, nur mit einem anderen Rücksprungziel
     * nach der Anmeldung — `returned()` kennt keine Menge, nur die öffentliche
     * Nummer der Ladung, die es anzeigen will.
     *
     * @return array{0: KeyUser, 1: string, 2: null}|array{0: null, 1: null, 2: RedirectResponse}
     */
    private function requireKeyForReturn(Request $request, string $reference): array
    {
        /** @var KeyUser|null $user */
        $user = Auth::guard("key")->user();

        if ($user === null) {
            return [null, null, redirect()
                ->to(KeymanagerLinks::login(route("account.checkout.returned", ["reference" => $reference]), $request))
                ->header("Cache-Control", "no-store, private")];
        }

        if ($user->temporary) {
            return [null, null, redirect()
                ->to(route("anonymous-token"))
                ->header("Cache-Control", "no-store, private")];
        }

        $key = $this->keyOf($user);
        if ($key === null) {
            return [null, null, redirect()
                ->to(route("account"))
                ->header("Cache-Control", "no-store, private")];
        }

        return [$user, $key, null];
    }

    /** Wortgleich zu AccountController::keyOf() — siehe dort für das Warum. */
    private function keyOf(KeyUser $user): ?string
    {
        $canonical = $user->getCanonicalKey();
        if ($canonical !== null && KeyIssuer::isKey($canonical)) {
            return strtolower($canonical);
        }

        return KeyIssuer::isKey($user->key) ? strtolower($user->key) : null;
    }

    /**
     * Ob dieses Formular von unserer eigenen Seite abgeschickt wurde.
     *
     * Wortgleich zu {@see KeyCreationController::sameOrigin()} und aus
     * demselben Grund; die Begründung steht dort.
     */
    private function sameOrigin(Request $request): bool
    {
        $origin = $request->header("Origin");

        if (is_string($origin) && $origin !== "" && $origin !== "null") {
            return $this->isOurs($request, $origin);
        }

        $site = $request->header("Sec-Fetch-Site");

        if (is_string($site) && $site !== "") {
            return in_array($site, ["same-origin", "same-site", "none"], true);
        }

        return true;
    }

    /** Ob ein URL auf den Host zeigt, unter dem diese Anfrage ankam. */
    private function isOurs(Request $request, string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null || $host === false) {
            return str_starts_with($url, "/") && !str_starts_with($url, "//");
        }

        $port = parse_url($url, PHP_URL_PORT);

        return ($host . ($port === null ? "" : ":" . $port)) === $request->getHttpHost();
    }
}
