<?php

namespace App\Http\Controllers;

use App\Authentication\CookieSupport;
use App\Authentication\KeyBackup;
use App\Authentication\KeyIssuer;
use App\Authentication\KeyUser;
use App\Authentication\LoginCodeIssuer;
use App\Landing\AppCallback;
use App\Landing\ChargeEligibility;
use App\Landing\KeymanagerLinks;
use App\Landing\KeyPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;

/**
 * Das Konto — /konto.
 *
 * Lag als `/keys/key/<uuid>` im Keymanager und ist die dritte und dickste
 * Seite des Schlüsselvorgangs, die hierher zieht; vorher die Anmeldung
 * ({@see LoginController}) und das Erstellen ({@see KeyCreationController}).
 *
 * **Der Schlüssel steht nicht mehr in der Adresse — für jeden, dessen
 * Browser das Cookie behält.** Das ist der Ertrag, und hier ist er größer als
 * bei den beiden Seiten davor: die alte Kontoadresse trug ihn im *Pfad*. Sie
 * stand damit in der Verlaufsliste, im Referer jeder von dort verlinkten
 * Seite und in jedem Bildschirmfoto einer Supportanfrage. Diese Seite liest
 * ihn aus dem Cookie, so wie jede andere hier
 * ({@see \App\Authentication\KeyAuthGuard}). Für einen Besucher ohne
 * Cookie-Unterstützung steht er absichtlich weiterhin in der Adresse —
 * {@see \App\Authentication\CookieSupport} trägt ihn von hier aus in jeden
 * weiteren Link und jedes Formular der Seite, statt ihn zu verstecken und
 * die Anmeldung beim nächsten Laden zu verlieren.
 *
 * **Es wird nichts nachgeladen, was nicht schon da wäre.** Ladung,
 * Verfallsdatum und die einzelnen Ladungen stehen alle in derselben Antwort,
 * die der Guard für diese Anfrage ohnehin geholt hat
 * ({@see KeyUser::getKeyData()}, zehn Sekunden zwischengespeichert). Die
 * Kontoseite kostet damit keinen Aufruf mehr als jede andere Seite auch.
 *
 * **Die Wahl des Pakets bleibt hier, der Bezahlvorgang selbst zieht um.**
 * Die Kacheln sind Preise, die {@see KeyPrice} vom Keyserver erfragt; ein
 * Klick geht auf `App\Http\Controllers\ChargeController` — /konto/aufladen,
 * ohne Schlüssel im Pfad. Alle Zahlarten laufen inzwischen dort.
 */
final class AccountController extends Controller
{
    /**
     * Wie oft ein Anmeldecode abgefragt werden darf.
     *
     * Der Übertragen-Dialog fragt im Sekundentakt, solange er offen ist, damit
     * er zugeht, sobald der Code verbraucht ist. Fünf Minuten offenstehen sind
     * 300 Anfragen; das Doppelte davon ist reichlich und begrenzt trotzdem,
     * was ein Skript aus einem angemeldeten Browser herausholen kann.
     */
    private const CODE_MAX_PER_WINDOW = 600;
    private const CODE_WINDOW_SECONDS = 300;

    public function show(Request $request): Response|RedirectResponse
    {
        /** @var KeyUser|null $user */
        $user = Auth::guard("key")->user();

        // Kein Schlüssel, also kein Konto. Zum Anmelden und mit dem Ziel im
        // Gepäck, damit der Weg dort endet, wo er begonnen hat — es gibt keine
        // Session, in der das sonst stehen könnte.
        if ($user === null) {
            return redirect()
                ->to(KeymanagerLinks::login(route("account"), $request))
                ->header("Cache-Control", "no-store, private");
        }

        // Die Web-Erweiterung meldet mit einem anonymen Token an, nicht mit dem
        // Schlüssel: wir erfahren ihn nie, und das ist der ganze Zweck der
        // Abmachung. Es gibt hier also kein Konto zu zeigen — die Erweiterung
        // zeigt es in ihrem eigenen Fenster. Dieselbe Antwort gibt die
        // Kontokachel (resources/views/parts/account-pill.blade.php).
        if ($user->temporary) {
            return redirect()
                ->to(route("anonymous-token"))
                ->header("Cache-Control", "no-store, private");
        }

        $key = $this->keyOf($user);

        // Der Schlüssel kam durch die Adresszeile — aus /keys/key/<uuid>, aus
        // einem alten Lesezeichen, aus dem Weiterleiten von /keys/key/enter.
        // Er bekommt hier sein Cookie und wird dann aus dem URL genommen: die
        // Seite, auf der jemand stehen bleibt, soll ihn nicht mehr tragen —
        // wenn das Cookie hält.
        //
        // Ohne die Marker-Prüfung wäre das eine Endlosschleife: withKeyCheck()
        // unten setzt den Schlüssel zusammen mit dem Marker wieder auf dieses
        // Ziel — /konto selbst —, und ohne haltendes Cookie träfe die nächste
        // Anfrage exakt dieselbe Bedingung wieder. Ist der Marker schon da,
        // wurde diese Prüfung bereits einmal gemacht; ein zweites Mal
        // umzuleiten sagt nichts Neues — die Cookie-Unterstützung des
        // Browsers ändert sich nicht zwischen zwei Aufrufen desselben
        // Controllers. Die Seite wird dann direkt gerendert, der Schlüssel
        // bleibt in der Adresse stehen, und CookieSupport::justAuthenticatedWithoutCookie()
        // entscheidet dort über den Hinweis.
        if ($request->filled("key") && $key !== null && $request->query(CookieSupport::MARKER) !== "1") {
            Cookie::queue(Cookie::forever("key", $key, "/", null, $request->isSecure(), false));

            // 302 und nicht 303: dies ist bereits ein GET, und das Ziel ist
            // derselbe Ort ohne die Zugangsberechtigung im URL. Die Marker der
            // App müssen mit, sonst geht der Rückweg in die App genau hier
            // verloren.
            //
            // withKeyCheck() adds the one-hop marker on top of whatever
            // CookieCarryingUrlGenerator already carried into route()'s
            // result: the cookie just queued above may not have survived,
            // and this is the hop that lets the account page notice and tell
            // a cookie-blind visitor so. See CookieSupport's docblock.
            return redirect()
                ->to(CookieSupport::withKeyCheck(route("account", AppCallback::markers($request)), $key))
                ->header("Cache-Control", "no-store, private");
        }

        $charge = $user->getCharge();

        // Der Custom Tab der App: hier endet die Anmeldung, also geht der
        // Schlüssel von hier über den verifizierten App Link zurück, statt dass
        // eine Seite gerendert wird. Vor allem anderen, was Zeit kostet — und
        // deshalb erst nach dem Cookie-Zweig oben, dessen Weiterleitung die
        // Marker weiterreicht.
        if (AppCallback::isHandback($request) && $key !== null) {
            return redirect()
                ->away(AppCallback::handbackUrl(
                    $key,
                    $request->input("keystore"),
                    $request->input("variant"),
                    // Ein Schlüssel ohne Guthaben kann noch nichts bezahlen;
                    // die App setzt den Benutzer dann gleich wieder auf den
                    // Aufladen-Abschnitt, statt den Tab nur zu schließen.
                    // Unbekannte Ladung zählt als leer: der Rat ist derselbe.
                    ($charge ?? 0) <= 0
                ))
                ->header("Cache-Control", "no-store, private");
        }

        $orders = $user->getChargeOrders();

        return response()
            ->view("account", [
                "title" => trans("titles.account"),
                "navbarFocus" => "login",
                "css" => [Vite::asset("resources/less/metager/pages/account.less")],
                "js" => [Vite::asset("resources/js/account.js")],

                "key" => $key,
                // Not $warning/$info: those are the layout's generic flash
                // slots (@extends passes every view variable up through
                // get_defined_vars(), and a page-local $warning would render
                // there a second time, raw and untranslated). See
                // CookieSupport::justAuthenticatedWithoutCookie().
                "cookieNotice" => CookieSupport::justAuthenticatedWithoutCookie($request)
                    ? trans("login.no_cookies_notice")
                    : null,
                "fingerprint" => $user->getKeyFingerprint(),
                "charge" => $charge,
                "state" => $user->getKeyState(),
                "expiration" => $user->getExpiration(),
                "orders" => $orders,

                // Der Keyserver hat nicht geantwortet. Kein Fehler, den der
                // Besucher verursacht hat, und kein Grund, ihm die Seite zu
                // verweigern — nur einer, die Zahlen nicht zu erfinden.
                "unreachable" => $charge === null,

                "settingsUrl" => $key === null ? null : KeyBackup::settingsUrl($request, $key),
                "qrUri" => $key === null ? null : KeyBackup::qrDataUri($key),

                "tiers" => $key === null ? [] : KeyPrice::tiers(),
                "topupBlocked" => ChargeEligibility::blockedReason($request, $user, $orders),

                "ordersUrl" => $key === null ? null : KeymanagerLinks::orders($key),
                "campaignsUrl" => $key === null ? null : KeymanagerLinks::campaigns($key),
                "logoutUrl" => KeymanagerLinks::remove(route("startpage")),
                "loginCodeUrl" => route("account.logincode", [], false),
                "searchUrl" => route("startpage"),
                "priceUrl" => route("price"),
                "helpUrl" => route("key-faq"),
                "membershipUrl" => route("membership_form"),
            ])
            // Auf dieser Seite steht ein Guthaben und ein Weg zum Schlüssel.
            // Nichts davon gehört in einen Cache, weder in einen gemeinsamen
            // noch in den des Browsers.
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Der Anmeldecode für den Übertragen-Dialog.
     *
     * Fragt für den *angemeldeten* Besucher; welcher Schlüssel gemeint ist,
     * steht im Cookie und in keinem Parameter. Der Keymanager hatte dafür
     * `GET /keys/key/<uuid>/logincode` — den Schlüssel im Pfad, damit ihn eine
     * Seite abfragen konnte, die ihn ohnehin schon anzeigte.
     *
     * Keine Herkunftsprüfung, und das ist Absicht: die Antwort ist JSON auf
     * eine GET-Anfrage ohne `Access-Control-Allow-Origin`, also kann eine
     * fremde Seite sie nicht lesen. Was sie kann, ist sie *auslösen*, und
     * dagegen steht die Bremse — ein Code, der ununterbrochen verlängert wird,
     * bliebe sonst beliebig lange gültig.
     */
    public function loginCode(Request $request, LoginCodeIssuer $issuer): JsonResponse
    {
        /** @var KeyUser|null $user */
        $user = Auth::guard("key")->user();
        $key = $user === null || $user->temporary ? null : $this->keyOf($user);

        if ($key === null) {
            return response()
                ->json(["code" => null], 401)
                ->header("Cache-Control", "no-store, private");
        }

        // Pro Schlüssel gezählt und nicht pro Adresse: den Dialog öffnet nur,
        // wer bereits angemeldet ist, und hinter einer gemeinsamen Adresse
        // sitzen mehrere Konten.
        $limiter = "account-logincode:" . $key;

        if (RateLimiter::tooManyAttempts($limiter, self::CODE_MAX_PER_WINDOW)) {
            return response()
                ->json(["code" => null], 429)
                ->header("Cache-Control", "no-store, private");
        }
        RateLimiter::hit($limiter, self::CODE_WINDOW_SECONDS);

        $code = $issuer->issue($key);

        return response()
            ->json(["code" => $code], $code === null ? 503 : 200)
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Der Schlüssel dieses Benutzers in der Form, die auf ein zweites Gerät
     * gehört.
     *
     * Bevorzugt die kanonische Form vom Keyserver — für einen alten
     * Nicht-UUID-Schlüssel ist das eine andere Zeichenkette als die im Cookie,
     * und nur sie funktioniert im Anmeldeformular. Antwortet der Keyserver
     * nicht, taugt das Cookie selbst, sofern es überhaupt ein Schlüssel ist:
     * die Seite kann dann keine Zahlen zeigen, aber immer noch den Weg zurück.
     */
    private function keyOf(KeyUser $user): ?string
    {
        $canonical = $user->getCanonicalKey();
        if ($canonical !== null && KeyIssuer::isKey($canonical)) {
            return strtolower($canonical);
        }

        return KeyIssuer::isKey($user->key) ? strtolower($user->key) : null;
    }

}
