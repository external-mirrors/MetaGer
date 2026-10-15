<?php

namespace App\Http\Controllers;

use App\Authentication\ChargeOrderIssuer;
use App\Authentication\KeyUser;
use App\Authentication\SumaPaymentsChargeIssuer;
use App\Http\Controllers\Concerns\HandlesKeyCheckout;
use App\Landing\ChargeEligibility;
use App\Landing\KeyPrice;
use App\Support\AppHosts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;

/**
 * Aufladen — /konto/aufladen/<menge>, ohne Schlüssel im Pfad.
 *
 * Der zweite Schritt des Bezahlvorgangs; {@see AccountController} bleibt der
 * erste (die Wahl des Pakets). Wie bei jedem Schritt davor steht der
 * Schlüssel in keiner Adresse — er kommt aus dem Cookie, über
 * {@see \App\Authentication\KeyAuthGuard}.
 *
 * **Bezahlt wird bei suma-payments** (suma-crm
 * docs/keymanager-migration-plan.md, Schritte 3 und 7): hier steht nur die
 * Zustimmung zum vorzeitigen Erlöschen des Widerrufsrechts ({@see sumaShow()}),
 * dann legt der Keyserver den Auftrag an und schickt den Besucher zur
 * Zahlweisenwahl dort ({@see sumaSubmit()}). Die eigenen Seiten für Bar,
 * Wero, micropayment und PayPal sind mit dem Umzug weg; `show()` leitet nur
 * noch weiter.
 *
 * Zurück kommt der Besucher auf {@see returned()} — kein Beleg, nur „bezahlt"
 * oder „wird noch bearbeitet", anhand des `paid`-Felds, das
 * {@see ChargeOrderIssuer::find()} mitbringt.
 *
 * **Wer zahlt, steht auf jeder Seite hier** — partials/key-fingerprint.blade.php,
 * dasselbe Kürzel wie auf /konto.
 */
final class ChargeController extends Controller
{
    use HandlesKeyCheckout;

    public function show(Request $request, int $amount): RedirectResponse
    {
        [$user, , $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        if (!$this->knownTier($amount) || ChargeEligibility::blockedReason($request, $user, $user->getChargeOrders()) !== null) {
            return redirect()->to(route("account") . "#charge");
        }

        return redirect()->route("account.checkout.suma", ["amount" => $amount]);
    }

    /**
     * The consent here, then suma-payments' picker
     * ({@see SumaPaymentsChargeIssuer}).
     */
    public function sumaShow(Request $request, int $amount): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->requireKey($request, $amount);
        if ($redirect !== null) {
            return $redirect;
        }

        if (!$this->knownTier($amount)) {
            return redirect()->to(route("account") . "#charge");
        }

        $error = $request->query("error");
        $error = in_array($error, ["unreachable", "consent"], true) ? $error : null;

        return $this->render("checkout.suma-payments", $request, $key, $amount, [
            "step" => 2,
            "error" => $error,
        ])->header("Content-Security-Policy", $this->redirectingProviderCsp((string) config("metager.metager.keymanager.suma_payments_host")));
    }

    public function sumaSubmit(Request $request, int $amount, SumaPaymentsChargeIssuer $issuer): RedirectResponse
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
                ->to(route("account.checkout.suma", ["amount" => $amount]) . "?error=consent", 303)
                ->header("Cache-Control", "no-store, private");
        }

        $order = $issuer->create($key, $amount, AppHosts::currentOrigin($request), app()->getLocale());
        if ($order === null) {
            return redirect()
                ->to(route("account.checkout.suma", ["amount" => $amount]) . "?error=unreachable", 303)
                ->header("Cache-Control", "no-store, private");
        }

        // A foreign host: the choice and the payment happen at suma-payments.
        return redirect()
            ->away($order["redirect_url"], 303)
            ->header("Cache-Control", "no-store, private");
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

        // Der Stand, den der Guard mitbringt, ist bis zu zehn Sekunden alt und
        // damit auf dieser Seite fast immer der von *vor* dieser Zahlung. Er
        // steht hier nicht nur einmal: unten in der Kachel, oben im Kontochip
        // und in der Seitenleiste — „Aufladen abgeschlossen" über „0 Token"
        // war genau das, was man lokal zu sehen bekam.
        //
        // Einmal verwerfen genügt für alle drei, weil sie denselben KeyUser
        // fragen. Und weil der neue Eintrag wieder zehn Sekunden hält, stimmt
        // auch die Seite danach — Suche oder Konto, je nachdem, wohin man von
        // hier aus weitergeht.
        //
        // Die eine zusätzliche Anfrage an den Keyserver fällt pro
        // abgeschlossener Zahlung an, nicht pro Seitenaufruf.
        $user->refresh();

        return response()
            ->view("checkout.returned", [
                "title" => trans("titles.checkout"),
                "navbarFocus" => "login",
                "css" => [
                    Vite::asset("resources/less/metager/pages/account.less"),
                    Vite::asset("resources/less/metager/pages/checkout.less"),
                ],
                "fingerprint" => $user->getKeyFingerprint(),
                "amount" => $order["amount"],
                "paid" => $order["paid"],
                // Das Ergebnis, wegen dessen der ganze Vorgang stattfand.
                //
                // Nur, wenn es die gerade gekaufte Menge schon enthält.
                // Nach dem refresh() oben ist der Stand frisch vom Keyserver,
                // aber frisch heißt nicht gutgeschrieben: `paid` sagt nur,
                // dass dort eine Zahlung zu dieser Ladung steht, und der
                // Browser kann von einer Weiterleitung zurück sein, bevor sie
                // verbucht ist. Ein Stand von *vor* der Gutschrift, groß
                // gesetzt unter „Aufladen abgeschlossen", wäre die eine Zahl,
                // die dieser Vorgang nicht falsch anzeigen darf; im Zweifel
                // nennt die Seite deshalb nur die gekaufte Menge.
                "balance" => $this->creditedBalance($user, $order["amount"]),
                "accountUrl" => route("account"),
                // Wo die Auftragsbestätigung liegt: auf der Bestelldetailseite,
                // die diese Nummer nachschlägt. Nur wenn schon bezahlt wurde —
                // vorher gibt es dort nichts herunterzuladen.
                "orderUrl" => route("account.orders.show", ["reference" => $order["public_id"]]),
                // Der eigentliche nächste Schritt nach einer geglückten
                // Aufladung: suchen. Die Seite bot ihn bisher nicht an, nur
                // den Weg zurück zum Konto — wer hier landet, ist fertig mit
                // dem Bezahlen und will los.
                "startpageUrl" => route("startpage"),
            ])
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Der Kontostand für {@see returned()} — oder null, solange er die gerade
     * gekaufte Menge noch nicht enthält.
     *
     * Die Prüfung ist bewusst „mindestens so groß wie gekauft" und nicht ein
     * Vergleich mit einem vorher gemerkten Stand: einen vorherigen Stand gibt
     * es hier nicht, dieser Vorgang kommt von einem anderen Ursprung zurück.
     *
     * Setzt voraus, dass {@see KeyUser::refresh()} vorher lief — sonst prüft
     * sie einen bis zu zehn Sekunden alten Stand und sagt „noch nicht
     * gutgeschrieben", wo längst gutgeschrieben ist.
     */
    private function creditedBalance(KeyUser $user, float|int $amount): ?float
    {
        $balance = $user->getCharge();

        return $balance !== null && $balance >= $amount ? $balance : null;
    }

    /**
     * Der Rahmen der Zustimmungsseite: Titel, Assets, wer zahlt, und der Weg
     * zurück zur Paketwahl.
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
                // Der Euro-Betrag des Pakets für partials/checkout-summary.
                // Jeder Aufrufer von render() hat $amount schon gegen
                // knownTier() geprüft, und das liest dieselbe Liste — der
                // Schlüssel ist hier also immer gesetzt. Das `?? null` ist die
                // Absicherung, die das Partial dann als „nur Tokenzahl"
                // rendert statt als „ €".
                "price" => KeyPrice::tiers()[$amount] ?? null,
                // Für die „Guthaben danach"-Spalte der Kurzfassung
                // (partials/checkout-summary). Null, wenn der Keyserver die
                // Frage gerade nicht beantwortet — dann fällt genau diese
                // Spalte weg, nicht die ganze Kurzfassung.
                "currentCharge" => $user->getCharge(),
                // Wo im Vorgang diese Seite steht (partials/checkout-steps).
                "step" => 2,
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
        return $this->resolveKey($request, route("account.checkout", ["amount" => $amount]));
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
        return $this->resolveKey($request, route("account.checkout.returned", ["reference" => $reference]));
    }

    /**
     * Die Standard-CSP, die build/nginx/configuration/nginx.conf (der `map`
     * für `$csp`) jeder Seite ohne eigene Policy mitgibt — hier wörtlich
     * wiederholt, weil ein selbst gesetzter `Content-Security-Policy`-Header
     * die ganze Policy *ersetzt*, nicht ergänzt. Muss mit nginx.conf Zeile für
     * Zeile gleichlaufen; {@see redirectingProviderCsp()} erweitert daran nur
     * `form-action`.
     */
    private const NGINX_DEFAULT_CSP = "default-src 'self'; script-src 'self'; script-src-elem 'self'; script-src-attr 'self'; worker-src 'self' blob:; style-src 'self'; style-src-elem 'self'; style-src-attr 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-src 'self'; frame-ancestors 'self' https://scripts.zdv.uni-mainz.de; form-action 'self' metager.org metager.de";

    /**
     * Die Standard-CSP mit einem um $providerHosts erweiterten `form-action`.
     *
     * {@see sumaShow()} beantwortet sein Zustimmungs-Formular mit einer
     * Weiterleitung (303, {@see sumaSubmit()}) auf die Zahlweisenwahl von
     * suma-payments. Chrome und Safari
     * prüfen `form-action` gegen *jede* Weiterleitung, die einem Formular-POST
     * folgt — steht der Anbieter-Host nicht in `form-action`, blockiert der
     * Browser den Sprung, der POST endet mit 303 und der Vorgang bleibt auf der
     * Zustimmungsseite stehen. Firefox prüft
     * `form-action` bei Weiterleitungen nicht — deshalb fiel es weder lokal noch
     * im Dusk-Lauf auf, die beide Firefox fahren.
     *
     * Ohne diesen Header gälte die nginx-Standard-CSP; deren `form-action 'self'
     * metager.org metager.de` kennt suma-payments nicht. Der Header hier
     * ersetzt sie für diese eine Seite — nginx setzt seinen `$csp` nur, wenn die
     * Antwort keinen eigenen mitbringt (der `map` in nginx.conf hat für den
     * nicht-leeren Fall keinen Zweig und damit keinen Wert).
     */
    private function redirectingProviderCsp(string $providerHosts): string
    {
        return str_replace(
            "form-action 'self' metager.org metager.de",
            "form-action 'self' metager.org metager.de " . $providerHosts,
            self::NGINX_DEFAULT_CSP,
        );
    }

    /**
     * Ob $amount eines der kaufbaren Pakete ist.
     *
     * {@see show()} prüft das vor der Weiterleitung; die Zustimmungsseite
     * wird auch direkt über ihre Adresse erreicht und braucht dieselbe
     * Prüfung, sonst rendert `/konto/aufladen/999/bezahlen` eine Seite für
     * einen Betrag, den es nicht zu kaufen gibt — und {@see render()} müsste
     * einen Preis für ihn finden.
     */
    private function knownTier(int $amount): bool
    {
        return array_key_exists($amount, KeyPrice::tiers());
    }
}
