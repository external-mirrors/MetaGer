<?php

namespace App\Landing;

use App\Localization;
use Illuminate\Http\Request;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Wohin die Links des Schlüsselvorgangs zeigen, in einer Datei.
 *
 * `/keys` ist keine MetaGer-Route: nginx reicht sie an den Keymanager weiter.
 * Was der noch besitzt, schrumpft mit jedem Umzugsschritt — inzwischen ist es
 * der Bezahlvorgang, die Bestellungen, die Gutscheinaktionen, das Abmelden und
 * die API. Erklärt, angemeldet, erstellt und verwaltet wird hier.
 *
 * Diese Datei ist deshalb keine Sammlung von `/keys`-URLs mehr, sondern die
 * Antwort auf „welcher Link im Schlüsselvorgang geht wohin“ — und die Hälfte
 * der Antworten lautet inzwischen: auf eine MetaGer-Route.
 *
 *   {@see login()}, {@see create()}, {@see account()}, {@see dashboard()},
 *   {@see accountForVisitor()}   MetaGer-Routen
 *
 *   {@see orders()}, {@see campaigns()}, {@see remove()},
 *   {@see voucher()}, {@see keyApi()}   noch drüben
 *
 * Dass die MetaGer-Routen hier stehen, obwohl sie `route()` nur weiterreichen,
 * hat einen Grund: jeder Aufrufer erreicht sie über dieselben zwei Marker
 * ({@see appCallback()}), und {@see accountForVisitor()} trifft dabei eine
 * Fallunterscheidung, die kein Aufrufer wiederholen soll. Welcher Link wohin
 * geht, in einer Datei — das ist der Zweck der Datei.
 *
 * Für die Pfade drüben muss ein *URL* gebaut werden statt eine Route benannt:
 * `URL::formatPathUsing` im AppServiceProvider setzt das Sprachpräfix vor alles,
 * was `route()` und `url()` erzeugen, aber für sie gibt es keine Route zu
 * benennen — das erledigt LaravelLocalization::getLocalizedURL.
 */
final class KeymanagerLinks
{
    /**
     * Die Callback-Marker der MetaGer-App, weitergereicht.
     *
     * Steht hier nur noch als Name: die Sache selbst ist
     * {@see AppCallback::markers()}, seit das Konto und damit auch die Rückgabe
     * des Schlüssels an die App hierher gezogen sind. Die Aufrufer sind alle
     * Links im Schlüsselvorgang, und die stehen in dieser Datei — der eine
     * Namensraum, in dem „welcher Link wohin geht“ steht, soll nicht zwei
     * Klassen nennen müssen.
     *
     * @return array<string, string>
     */
    public static function appCallback(?Request $request = null): array
    {
        return AppCallback::markers($request);
    }

    /** @param array<string, string> $query */
    private static function url(string $path, array $query = [], string $fragment = ""): string
    {
        $url = LaravelLocalization::getLocalizedURL(null, "/keys" . $path);

        if ($query !== []) {
            $url .= "?" . http_build_query($query);
        }

        return $url . $fragment;
    }

    /**
     * Creating a key — MetaGer's own `/schluessel-erstellen`.
     *
     * This used to be `/keys/key/create`, and unlike the sign-in page nothing
     * of it stayed behind: that route redirects here in both of its branches,
     * the one for a visitor with a cookie included. Deciding whether somebody
     * should be creating a key at all is a question about the visitor, and this
     * is the side that has the visitor.
     *
     * Kept here rather than spelled out at its five call sites for the same
     * reason {@see login()} is: both are reached through the app's two markers.
     * The `#second-nav` anchor the old link carried is gone with the page that
     * had a second nav.
     */
    public static function create(?Request $request = null): string
    {
        return route("key-create", self::appCallback($request));
    }

    /**
     * Signing in with a key that already exists — MetaGer's own `/anmelden`.
     *
     * This used to be `/keys/key/enter`, and that URL still answers: it is
     * where a visitor who *already* has a key is sent ({@see dashboard()}), and
     * the MetaGer app opens it directly. What moved here is the page — and, in
     * a second step, the sign-in itself.
     *
     * `$redirectSuccess` rides along as a query parameter and the page puts it
     * back on the form, because the visitor is not signed in until that POST
     * and there is no session to keep it in.
     */
    public static function login(?string $redirectSuccess = null, ?Request $request = null): string
    {
        $query = self::appCallback($request);

        if ($redirectSuccess !== null) {
            $query["redirect_success"] = $redirectSuccess;
        }

        return route("login", $query);
    }

    /**
     * Das Konto — MetaGers eigenes `/konto`.
     *
     * Lag als `/keys/key/<uuid>` im Keymanager und ist die dritte Seite des
     * Schlüsselvorgangs, die hierher gezogen ist. Der Schlüssel steht nicht
     * mehr im Pfad und in keinem Parameter: das Konto liest ihn aus dem
     * Cookie, so wie jede andere Seite hier auch
     * ({@see \App\Authentication\KeyAuthGuard}).
     *
     * Deshalb nimmt diese Methode auch keinen Schlüssel mehr entgegen. Ihre
     * beiden Aufrufer im Anmelde- und Erstellvorgang setzen das Cookie in
     * derselben Antwort, die diese Weiterleitung ist — der nächste Aufruf ist
     * angemeldet, ganz ohne dass die Zugangsberechtigung durch die Adresszeile
     * reist.
     *
     * `$callback` trägt die Marker der App, wenn es welche gibt, und sie sind
     * der Grund, warum das Konto in dem Fall überhaupt das Ziel ist: dort
     * geschieht die Rückgabe an den Custom Tab, und dort ist die Ladung
     * bekannt, an der sich entscheidet, ob die App gleich zum Aufladen
     * weiterschicken soll ({@see AppCallback::handbackUrl()}).
     *
     * @param array<string, string> $callback
     */
    public static function account(array $callback = []): string
    {
        return route("account", $callback);
    }

    /**
     * Where the sign-in page asks what a key is worth, ending in a slash so the
     * key itself can be appended.
     *
     * The one place the browser talks to the keymanager directly rather than
     * through App\Authentication\KeyUser: this question is asked *before* the
     * visitor is signed in, about a key this side has never seen, and the
     * answer only decides whether to show a confirmation. `GET /api/json/key/:key`
     * needs no bearer token for exactly that reason: it answers about a key the
     * caller already holds, and says only what it is worth.
     *
     * It is not rate limited. `keyIpLimitMiddleware`, which sits on that route
     * and reads like one, is an IP allowlist for the handful of keys named in
     * the keymanager's `key_ip_limits` config — it lets every other key through
     * untouched. The only brake is a deliberate 250 ms delay on unauthorized
     * callers.
     */
    public static function keyApi(): string
    {
        // Der Schrägstrich wird angehängt und nicht mitgegeben:
        // LaravelLocalization::getLocalizedURL normalisiert einen abschließenden
        // Schrägstrich weg, und die Seite hängt den Schlüssel direkt an.
        return rtrim(self::url("/api/json/key"), "/") . "/";
    }

    /**
     * Das Konto für *diesen* Besucher — mit dem Schlüssel, den er mitbringt.
     *
     * Der Unterschied zu {@see dashboard()} ist ein Fall, der leicht zu
     * übersehen ist und sich als Endlosschleife zeigt: die Anmelde- und die
     * Erstellen-Seite schicken einen Besucher, der schon einen Schlüssel hat,
     * ins Konto — und „hat einen Schlüssel“ heißt dort auch „hat ihn in der
     * Query oder in einem Header“, etwa aus einem gespeicherten Anmelde-URL.
     * Ein solcher Schlüssel steht in keinem Cookie. Das Konto fände ihn nicht
     * und schickte den Besucher zurück zum Anmelden.
     *
     * Solange das Konto im Keymanager lag, fiel das nicht auf: `/key/enter`
     * las notfalls den *Referer* der Anfrage und fischte den Schlüssel aus dem
     * URL, von dem der Besucher kam. Das ist hier nicht nachgebaut — ein
     * Referer ist nichts, worauf eine Anmeldung sich stützen sollte. Der
     * Schlüssel wird stattdessen weitergereicht, und das Konto nimmt ihn aus
     * der Adresse heraus, sobald es sein Cookie gesetzt hat.
     *
     * Die Reihenfolge ist die des {@see \App\Authentication\KeyAuthGuard}:
     * Query vor Header vor Cookie. Kam der Schlüssel aus dem Cookie, reist er
     * gar nicht — das Cookie gilt für /konto ohnehin.
     */
    public static function accountForVisitor(Request $request): string
    {
        $query = self::appCallback($request);

        $carried = $request->input("key");
        if (!is_string($carried) || trim($carried) === "") {
            $carried = $request->header("key");
        }

        if (is_string($carried) && trim($carried) !== "") {
            $query["key"] = trim($carried);
        }

        return self::account($query);
    }

    /**
     * Wohin ein bereits angemeldeter Besucher geht — dasselbe `/konto`.
     *
     * Zwei Namen für eine Adresse, und der Unterschied ist die Frage, die der
     * Aufrufer stellt: {@see account()} ist „das Konto“, das hier ist „wo bin
     * ich, wenn ich schon angemeldet bin“. Solange das Konto im Keymanager
     * lag, waren es zwei verschiedene URLs — `/keys/key/<uuid>` und
     * `/keys/key/enter`, weil nur jener Dienst einen alten Nicht-UUID-Schlüssel
     * auf sein Konto abbilden konnte. Diese Umrechnung macht inzwischen
     * {@see \App\Authentication\KeyUser::getKeyData()} über die API, und damit
     * fällt die Unterscheidung weg.
     *
     * Der Name bleibt, weil die Kontokachel, die Seitenleiste und der Hinweis
     * auf der Startseite ihn benutzen und „das Konto“ und „dorthin, wo ich
     * angemeldet bin“ an diesen Stellen verschiedene Sätze sind.
     */
    public static function dashboard(?Request $request = null): string
    {
        return self::account(self::appCallback($request));
    }

    /**
     * Signing out, and coming back to the page the user is standing on.
     *
     * The return URL has to have `key` taken out of it first, and that is the
     * whole reason this method exists rather than the blade building the link.
     * Entering a key redirects to `…/?key=<uuid>` — routes/key.js puts it there
     * so the guard picks the key up on the very next request — and
     * resources/js/utility.js then rewrites it back out of the address bar. The
     * sidebar, though, was rendered from the URL as it arrived, so its logout
     * link still carried the parameter. Signing out cleared the cookie and
     * bounced straight back to a URL that still held the credential, and
     * `KeyAuthGuard` reads the query string ahead of the cookie: the visitor
     * landed signed in again, on a URL that looked clean. Only a second,
     * unassisted load of the page finally logged them out.
     *
     * The keymanager strips it a second time (pass/app/LogoutRedirect.js),
     * because `/key/remove` also falls back to the Referer when no `url` is
     * given, and a Referer is not ours to sanitise.
     */
    public static function remove(?string $returnTo = null): string
    {
        $returnTo ??= Localization::currentFullUrl();

        return self::url("/key/remove", ["url" => self::withoutKey($returnTo)]);
    }

    /**
     * `$url` minus its `key` parameter.
     *
     * Split by hand rather than through `parse_url()`/`http_build_url()`: the
     * value comes from the request we are serving, so it is already a URL, and
     * reassembling one from eight optional parts is the step that drops a port
     * or an empty path.
     */
    private static function withoutKey(string $url): string
    {
        $fragment = "";
        if (($hash = strpos($url, "#")) !== false) {
            $fragment = substr($url, $hash);
            $url = substr($url, 0, $hash);
        }

        $mark = strpos($url, "?");
        if ($mark === false) {
            return $url . $fragment;
        }

        parse_str(substr($url, $mark + 1), $query);
        unset($query["key"]);

        return substr($url, 0, $mark)
            . ($query === [] ? "" : "?" . http_build_query($query))
            . $fragment;
    }

    /**
     * Die Bestellungen zu einem Schlüssel — Rechnungen, Belege, Erstattungen.
     *
     * Noch nicht umgezogen; das Konto verlinkt dorthin, so wie es vorher einen
     * Reiter dafür hatte.
     */
    public static function orders(string $key): string
    {
        return self::url("/key/" . urlencode($key) . "/orders");
    }

    /**
     * Die Aktionen zu einem Schlüssel — Gutscheinkarten, die jemand für andere
     * anlegt und deren Guthaben von diesem Schlüssel getragen wird.
     *
     * Ebenfalls noch nicht umgezogen. Das Konto verlinkt sie unauffälliger als
     * der alte Reiter es tat: die allermeisten Schlüssel haben keine, und ein
     * gleichrangiger dritter Reiter behauptete etwas anderes.
     */
    public static function campaigns(string $key): string
    {
        return self::url("/key/" . urlencode($key) . "/campaigns");
    }

    /**
     * Redeeming a voucher code — the campaign pages, which stay here.
     *
     * The last of the reader-facing `/keys` paths still linked from a MetaGer
     * page: the key FAQ explains what to do with a promotional card. It moves
     * in step 3 with the rest of the key flow.
     */
    public static function voucher(): string
    {
        return self::url("/c");
    }
}
