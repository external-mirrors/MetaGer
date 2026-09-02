<?php

namespace App\Http\Controllers;

use App\Authentication\CookieSupport;
use App\Authentication\KeyBackup;
use App\Authentication\KeyIssuer;
use App\Landing\KeymanagerLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;

/**
 * Einen Schlüssel erstellen — /schluessel-erstellen, beide Richtungen.
 *
 * Lag als /keys/key/create im Keymanager und ist die zweite Seite des
 * Schlüsselvorgangs, die hierher zieht; die erste war die Anmeldung
 * ({@see LoginController}). Der Grund ist derselbe und er ist nicht
 * Ordnungsliebe: solange der andere Dienst das Cookie setzte, musste er den
 * Besucher anschließend zurückreichen, und der Schlüssel reiste dafür durch die
 * Adresszeile. Auf dieser Seite war es sogar noch etwas ärger — sie schrieb ihn
 * per `history.replaceState` selbst dorthin, mitsamt Verlauf.
 *
 * Beim Keyserver blieb genau eine Frage, als `POST /api/json/key/new`: eine
 * UUID, die noch niemandem gehört ({@see KeyIssuer}). Alles andere hängt an
 * nichts, was nur er hat.
 *
 * **Die Seite hat zwei Zustände, und der ohne Javascript ist der zweite.** Das
 * Markup zeigt den Schlüssel; resources/js/key-create.js blendet ihn wieder aus
 * und stellt den Knopf davor. Das ist kein Zierrat: die Seite fragt damit
 * einmal nach, bevor jemand einen *zweiten* Schlüssel bekommt. Wer sein Cookie
 * verloren hat und hier landet, hat kein Konto verloren — sein Guthaben hängt
 * am alten Schlüssel, und ein neuer bekommt ein eigenes, getrenntes. Genau
 * dieser Fall ist der, den der Support regelmäßig zu hören bekommt.
 */
final class KeyCreationController extends Controller
{
    /**
     * Was schiefgehen kann, und was die Seite dazu sagt.
     *
     * Aufgezählt aus demselben Grund wie in {@see LoginController::ERRORS}: der
     * Wert steht in der Query und wird zu einem Übersetzungsschlüssel.
     */
    private const ERRORS = [
        // Der Keyserver hat nicht geantwortet. Der einzige Fehler hier, bei dem
        // „gleich noch einmal“ ein sinnvoller Rat ist.
        "keyserver_unreachable",
        // Zu viele Aufrufe von dieser Adresse — siehe MAX_PER_WINDOW.
        "too_many_attempts",
        // Das versteckte Feld trug keinen Schlüssel. Kein Tippfehler eines
        // Besuchers, sondern eine Seite, die zu lange offen lag, oder ein
        // Formular, das jemand von Hand nachgebaut hat.
        "no_key",
    ];

    /**
     * Wie oft von einer Adresse aus ein Schlüssel geholt werden darf.
     *
     * Jeder Aufruf dieser Seite ist eine Frage an den Keyserver. Sie ist billig
     * — ein EXISTS in seinem Redis —, aber sie ist von außen kostenlos
     * auslösbar, und das ist der Unterschied zu jeder anderen Seite hier.
     *
     * Reichlich bemessen: einen Schlüssel erstellt man einmal, und hinter einer
     * gemeinsamen Adresse tun es vielleicht ein paar Menschen an einem
     * Nachmittag. Sechzig in fünf Minuten ist nichts, was jemand versehentlich
     * erreicht.
     */
    private const MAX_PER_WINDOW = 60;
    private const WINDOW_SECONDS = 300;

    public function show(Request $request, KeyIssuer $issuer): Response|RedirectResponse
    {
        // Wer schon einen Schlüssel hat, will keinen zweiten. Dieselbe Prüfung
        // wie auf der Anmeldeseite und in derselben Reihenfolge wie im
        // KeyAuthGuard; ob das Mitgebrachte ein gültiger Schlüssel ist, weiß
        // nur der Keyserver, und der wird beim Weiterleiten ohnehin gefragt.
        if (
            $request->filled("key")
            || $request->hasHeader("key")
            || $request->cookie("key") !== null
        ) {
            return redirect()->to(KeymanagerLinks::accountForVisitor($request));
        }

        // Was mitgebracht wurde, gilt nur, solange nichts Aktuelleres dazukommt:
        // „hier ist ein neuer“ neben einer Seite ohne Schlüssel wäre eine
        // Meldung, die der Seite widerspricht, auf der sie steht.
        $error = $request->query("key_error");
        $error = is_string($error) && in_array($error, self::ERRORS, true) ? $error : null;

        $key = null;

        if (RateLimiter::tooManyAttempts($this->attemptKey($request), self::MAX_PER_WINDOW)) {
            $error = "too_many_attempts";
        } else {
            RateLimiter::hit($this->attemptKey($request), self::WINDOW_SECONDS);
            $key = $issuer->issue();

            if ($key === null) {
                $error = "keyserver_unreachable";
            }
        }

        // Zwei URLs, und dass es zwei sind, ist der Punkt — die Begründung
        // steht in {@see KeyBackup}, weil das Konto dieselben beiden anbietet.
        $settingsUrl = $key === null ? null : KeyBackup::settingsUrl($request, $key);

        return response()
            ->view("key-create", [
                "title" => trans("titles.key-create"),
                "navbarFocus" => "login",
                "css" => [Vite::asset("resources/less/metager/pages/key-create.less")],
                "js" => [Vite::asset("resources/js/key-create.js")],

                // Auf sich selbst und als Pfad, wie beim Anmeldeformular:
                // dieselbe Anwendung antwortet auf metager.de, metager.org,
                // metager3.de und einer .onion-Adresse.
                "action" => route("key-create", [], false),
                "key" => $key,
                "keyError" => $error,
                "settingsUrl" => $settingsUrl,
                "qrUri" => $key === null ? null : KeyBackup::qrDataUri($key),
                "callback" => KeymanagerLinks::appCallback($request),
                "loginUrl" => KeymanagerLinks::login(null, $request),
            ])
            // Auf dieser Seite steht ein Schlüssel. Er gehört in keinen Cache,
            // weder in einen gemeinsamen noch in den des Browsers.
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * „Ja, den nehme ich.“
     *
     * Hier wird das Cookie gesetzt, und das ist der ganze Umzug. Vorher tat es
     * das Konto im Keymanager, und der Schlüssel musste deshalb als `?key=` bis
     * dorthin reisen.
     */
    public function submit(Request $request): RedirectResponse
    {
        // Webrouten laufen ohne Session, es gibt also kein CSRF-Token. Ohne
        // Herkunftsprüfung wäre ein fremdes Formular, das hierher abschickt,
        // eine Möglichkeit, einen Besucher an einem Schlüssel anzumelden, den
        // jemand anderes kennt — und von da an liest und zahlt der mit.
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        $key = $request->input("key");
        $key = is_string($key) ? strtolower(trim($key)) : "";

        if (!KeyIssuer::isKey($key)) {
            return $this->back($request, "no_key");
        }

        // So wie der Keymanager es gesetzt hat: fünf Jahre, `lax`, und lesbar
        // für Skripte, weil die Web-Erweiterung es über die Cookie-Schnittstelle
        // des Browsers verfolgt. Unverschlüsselt, weil `key` in
        // EncryptCookies::$except steht — der Keymanager liest es unter
        // demselben Host.
        Cookie::queue(Cookie::forever("key", $key, "/", null, $request->isSecure(), false));

        // Auf das Konto, in den Abschnitt zum Aufladen: ein neuer Schlüssel hat
        // kein Guthaben, und ohne Guthaben ist er noch nichts wert. Die
        // Callback-Marker der App reisen mit — das Konto ist die Stelle, an der
        // ein Custom Tab den Schlüssel zurückgibt.
        //
        // withKeyCheck() rides along for the same reason it does in
        // LoginController::signIn(): the cookie just queued above may not
        // survive the round trip, and this is the one hop that can still hand
        // a cookie-blind visitor their brand-new key back. See
        // CookieSupport's docblock.
        return redirect()
            ->away(CookieSupport::withKeyCheck(
                KeymanagerLinks::account(KeymanagerLinks::appCallback($request)) . "#charge",
                $key
            ))
            ->header("Cache-Control", "no-store, private");
    }

    /** Zurück auf die Seite, mit einer Meldung und ohne verlorene Marker. */
    private function back(Request $request, string $error): RedirectResponse
    {
        $query = KeymanagerLinks::appCallback($request);
        $query["key_error"] = $error;

        // 303: das Ziel ist eine Seite, die der Browser mit GET holen soll,
        // statt den fehlgeschlagenen Versuch zu wiederholen.
        return redirect()
            ->to(route("key-create", $query), 303)
            ->header("Cache-Control", "no-store, private");
    }

    /** Gezählt wird pro Adresse; einen Benutzer gibt es hier noch nicht. */
    private function attemptKey(Request $request): string
    {
        return "key-create:" . $request->ip();
    }

    /**
     * Ob dieses Formular von unserer eigenen Seite abgeschickt wurde.
     *
     * Wortgleich zu {@see LoginController::sameOrigin()} und aus demselben
     * Grund; die Begründung steht dort.
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
