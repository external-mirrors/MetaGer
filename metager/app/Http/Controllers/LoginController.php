<?php

namespace App\Http\Controllers;

use App\Authentication\CookieSupport;
use App\Authentication\KeyResolver;
use App\Landing\KeymanagerLinks;
use App\Support\Browser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;

/**
 * Die Anmeldung — /anmelden, beide Richtungen.
 *
 * Lag als /keys/key/enter im Keymanager, erst nur als Seite, inzwischen auch
 * als Vorgang. Was dort bleiben musste, ist eine einzige Frage — was eine
 * Eingabe überhaupt ist ({@see KeyResolver}) —, und die wird über die API
 * gestellt. Alles andere passiert hier: Formular, Prüfung, Cookie, Ziel.
 *
 * Der Umzug des Vorgangs ist nicht Ordnungsliebe. Solange der Keymanager das
 * Cookie setzte, musste er den Besucher anschließend zurückreichen, und der
 * Schlüssel reiste dafür als `?key=` durch die Adresszeile — in den Verlauf
 * und in jeden Referer der nächsten Seite. Das Cookie hier zu setzen macht
 * diesen Umweg für jeden überflüssig, dessen Browser es behält. Mit ihm
 * entfallen `redirect_error` und dessen Herkunftsprüfung.
 *
 * **Für einen Besucher, dessen Browser das Cookie nicht behält, bleibt der
 * Schlüssel jetzt absichtlich sichtbar in der Adresse** — nicht nur auf
 * diesem einen Sprung, sondern auf jeder folgenden Seite, solange
 * {@see \App\Authentication\CookieSupport::keyMissingCookie()} das so sieht.
 * Es gibt kein Skript mehr, das ihn nachträglich aus der Adressleiste nimmt
 * (resources/js/utility.js tat das früher ungeprüft, unabhängig davon, ob das
 * Cookie tatsächlich gesetzt wurde — das hätte einem Besucher ohne
 * Cookie-Unterstützung nach einem Neuladen genau den Schlüssel wieder
 * entzogen, mit dem er angemeldet war). Cookieloses Arbeiten mit sichtbarem
 * Schlüssel ist die akzeptierte Alternative, nicht ein Zustand, der noch
 * wegzuräumen wäre.
 *
 * Zwei Dinge muss das Formular trotzdem weiterreichen, weil es auf Webrouten
 * keine Session gibt: wohin bei Erfolg (`redirect_success`) und die beiden
 * Callback-Marker der MetaGer-App.
 */
final class LoginController extends Controller
{
    /**
     * Die Fehlercodes, die der Keymanager als `key_error` zurückschicken kann.
     *
     * Eine Aufzählung und keine freie Zeichenkette: der Wert kommt aus der
     * Query und wird zu einem Übersetzungsschlüssel. Ohne diese Liste wäre
     * `?key_error=irgendwas` ein Weg, „login.errors.irgendwas“ nachzuschlagen —
     * Laravel gibt dann den Schlüssel selbst aus, und auf der Seite stünde
     * fremder Text.
     */
    private const ERRORS = [
        "invalid_key",
        "invalid_login_code",
        "invalid_key_payment_id",
        "no_input",
        "file_unreadable",
        // Sechs Zeichen, die kein Schlüssel sind — fast immer das Kürzel neben
        // dem Guthaben, das der Keyserver seit der Prüfung in resolve abweist
        // statt daraus ein leeres Phantomkonto zu machen.
        "key_mark",
        // Der Keyserver hat nicht geantwortet. Kein Urteil über die Eingabe,
        // und deshalb der einzige Fehler hier, bei dem „noch einmal versuchen“
        // ein sinnvoller Rat ist.
        "keyserver_unreachable",
        // Zu viele Versuche von dieser Adresse. Sechs Ziffern sind wenig, und
        // ohne Bremse wäre das Formular eine Maschine, an der man sie durchgeht.
        "too_many_attempts",
    ];

    /**
     * Was von einem abgewiesenen Versuch wieder ins Feld darf.
     *
     * Der Keymanager hängt die Eingabe als `invalid_key` an, damit ein
     * Tippfehler sichtbar bleibt. Ein Schlüssel hat 36 Zeichen, ein Anmeldecode
     * sechs; alles darüber ist keine Eingabe mehr, die jemand korrigieren will.
     */
    private const MAX_PREFILL = 64;

    public function show(Request $request): Response|RedirectResponse
    {
        // Wer schon einen Schlüssel hat, will nicht das Anmeldeformular,
        // sondern sein Konto — genau die Fallunterscheidung, die vorher in
        // routes/key.js stand. Die Reihenfolge ist die des KeyAuthGuard, und
        // geprüft wird nur, *ob* etwas da ist: was davon ein gültiger
        // Schlüssel ist, weiß nur der Keyserver, und der wird beim
        // Weiterleiten ohnehin gefragt.
        if (
            $request->filled("key")
            || $request->hasHeader("key")
            || $request->cookie("key") !== null
        ) {
            return redirect()->to(KeymanagerLinks::accountForVisitor($request));
        }

        $callback = KeymanagerLinks::appCallback($request);
        $redirectSuccess = $request->query("redirect_success");
        $redirectSuccess = is_string($redirectSuccess) && trim($redirectSuccess) !== ""
            ? $redirectSuccess
            : null;

        $error = $request->query("key_error");
        $error = is_string($error) && in_array($error, self::ERRORS, true) ? $error : null;

        $prefill = $request->query("invalid_key");
        $prefill = is_string($prefill) ? mb_substr(trim($prefill), 0, self::MAX_PREFILL) : "";

        return response()
            ->view("login", [
                "title" => trans("titles.login"),
                "navbarFocus" => "login",
                "css" => [Vite::asset("resources/less/metager/pages/login.less")],
                "js" => [Vite::asset("resources/js/login.js")],

                // Auf sich selbst, und als Pfad: dieselbe Anwendung antwortet
                // auf metager.de, metager.org, metager3.de und einer
                // .onion-Adresse, und ein Formular, das den Host nennt, ist nur
                // eine Art, den Besucher von seinem herunterzuschicken. Den
                // absoluten URL brauchte der Keymanager für seine
                // Herkunftsprüfung; die ist mit ihm entfallen.
                "action" => route("login", [], false),
                "chargeEndpoint" => KeymanagerLinks::keyApi(),
                "redirectSuccess" => $redirectSuccess,
                "callback" => $callback,

                // $keyError und nicht $error: @extends reicht die Variablen der
                // Ansicht per get_defined_vars() ans Layout weiter, und
                // layouts/staticPages rendert ein $error als Fehlerleiste. Der
                // Code stünde dann roh und unübersetzt ein zweites Mal auf der
                // Seite. Dasselbe gilt für $info, $success und $warning.
                "keyError" => $error,
                "prefill" => $prefill,

                "createUrl" => KeymanagerLinks::create($request),
                "tokenUrl" => route("anonymous-token"),
                "extension" => $this->extension(),
            ])
            // Die Seite hängt an Cookie und Query und trägt im Fehlerfall die
            // Eingabe des Besuchers. Nichts davon gehört in einen Cache, weder
            // in einen gemeinsamen noch in den des Browsers.
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Wie oft von einer Adresse aus geraten werden darf, und in welchem Fenster.
     *
     * Ein Anmeldecode ist sechs Ziffern und zehn Sekunden gültig. Das ist eine
     * Million Möglichkeiten, aber zu jedem Zeitpunkt sind einige davon echt,
     * und ohne Bremse ist das Formular die Maschine, die sie durchprobiert.
     * Vorher gab es hier gar nichts — POST /key/enter im Keymanager war
     * ungebremst, und die API dahinter ist es immer noch, weil dort jeder
     * Aufruf derselbe Aufrufer ist. Wessen Browser fragt, weiß nur diese Seite.
     *
     * Großzügig genug, dass niemand es bemerkt, der einen Schlüssel abtippt und
     * sich dabei zweimal vertut.
     */
    private const MAX_ATTEMPTS = 20;
    private const ATTEMPT_WINDOW_SECONDS = 300;

    /**
     * Ein Anmeldeversuch.
     *
     * Was die Eingabe ist, beantwortet der Keyserver ({@see KeyResolver});
     * was daraufhin geschieht, steht hier. Bei Erfolg wird das Cookie hier
     * gesetzt — das ist der ganze Grund, warum der Vorgang umgezogen ist, und
     * es ist der Grund, warum in keiner Weiterleitung mehr ein `?key=` steht.
     */
    public function submit(Request $request, KeyResolver $resolver): RedirectResponse
    {
        $callback = KeymanagerLinks::appCallback($request);
        $redirectSuccess = $request->input("redirect_success");
        $redirectSuccess = is_string($redirectSuccess) && trim($redirectSuccess) !== ""
            ? $redirectSuccess
            : null;

        // Webrouten laufen ohne Session, es gibt also kein CSRF-Token. Für ein
        // Anmeldeformular ist das nicht gleichgültig: ein fremdes Formular, das
        // hierher abschickt, meldet den Besucher an *seinem* Schlüssel an, und
        // von da an zahlt und liest der Angreifer mit. Also die Herkunft, so
        // wie sie der Browser selbst mitschickt.
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        if (RateLimiter::tooManyAttempts($this->attemptKey($request), self::MAX_ATTEMPTS)) {
            return $this->back($callback, $redirectSuccess, "too_many_attempts");
        }
        RateLimiter::hit($this->attemptKey($request), self::ATTEMPT_WINDOW_SECONDS);

        $entered = $request->input("key");
        $entered = is_string($entered) ? trim($entered) : "";
        $file = $request->file("file");

        if ($entered !== "") {
            $answer = $resolver->resolve($entered);
        } elseif ($file !== null && $file->isValid()) {
            $answer = $resolver->resolveImage($file);
        } else {
            return $this->back($callback, $redirectSuccess, "no_input");
        }

        return match ($answer["result"]) {
            KeyResolver::KEY => $this->signIn($request, $answer["key"], $callback, $redirectSuccess),
            // Der Gutschein wird auf der Kampagnenseite des Keymanagers
            // eingelöst; die kennt die Kampagne, das Budget und die Bremse
            // dafür. Hier ist nur bekannt, dass die Eingabe einer war.
            KeyResolver::VOUCHER => redirect()->away(KeymanagerLinks::voucher($answer["code"])),
            KeyResolver::UNREACHABLE
                => $this->back($callback, $redirectSuccess, "keyserver_unreachable", $entered),
            default => $this->back($callback, $redirectSuccess, $answer["error"], $entered),
        };
    }

    /**
     * Angemeldet: das Cookie setzen und den Besucher dorthin schicken, wo er
     * hin wollte.
     *
     * Das Cookie so, wie der Keymanager es gesetzt hat — fünf Jahre, `lax`,
     * und lesbar für Skripte, weil die Web-Erweiterung es über die
     * Cookie-Schnittstelle des Browsers verfolgt (TokenManager.js). Es wird
     * nicht verschlüsselt: `key` steht in EncryptCookies::$except, weil auch
     * der Keymanager unter demselben Host es lesen können muss.
     *
     * @param array<string, string> $callback
     */
    private function signIn(
        Request $request,
        string $key,
        array $callback,
        ?string $redirectSuccess
    ): RedirectResponse {
        Cookie::queue(Cookie::forever(
            "key",
            $key,
            "/",
            null,
            $request->isSecure(),
            false
        ));

        // One hop only: this Set-Cookie may not survive the round trip, and
        // the only way to know — or to keep a visitor whose browser drops it
        // authenticated on the very next page — is to hand them the key back
        // here. See CookieSupport's docblock. Every branch of afterSignIn()
        // resolves to one of our own routes, never an external app link, so
        // this is safe to apply unconditionally.
        //
        // withKeyCheck() is called by hand rather than left to
        // CookieCarryingUrlGenerator: this response is built with
        // redirect()->away(), which never touches the URL generator, so a
        // future change to redirect()->route() here would need to drop this
        // call rather than silently losing it.
        return redirect()
            ->away(CookieSupport::withKeyCheck($this->afterSignIn($request, $key, $callback, $redirectSuccess), $key))
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Wohin nach einer erfolgreichen Anmeldung.
     *
     * Drei Ziele, in dieser Reihenfolge:
     *
     * 1. Die App. Trägt die Anfrage die Callback-Marker, dann läuft das hier in
     *    einem Custom Tab, und der Schlüssel muss zurück in die App. Das tut das
     *    Konto — dort steht die Weiche, die daraus einen App-Link macht, und
     *    dort steht auch die Ladung, an der sie entscheidet, ob die App gleich
     *    zum Aufladen weiterschicken soll.
     * 2. Die Seite, von der der Besucher kam, wenn sie unsere ist. Ohne `?key=`:
     *    das Cookie liegt schon in derselben Antwort, die diese Weiterleitung
     *    ist, also ist der nächste Aufruf angemeldet.
     * 3. Sonst das Konto.
     *
     * @param array<string, string> $callback
     */
    private function afterSignIn(
        Request $request,
        string $key,
        array $callback,
        ?string $redirectSuccess
    ): string {
        if ($callback !== []) {
            return KeymanagerLinks::account($callback);
        }

        if ($redirectSuccess !== null && $this->isOurs($request, $redirectSuccess)) {
            return $redirectSuccess;
        }

        return KeymanagerLinks::account();
    }

    /**
     * Ob ein URL auf denselben Host zeigt, unter dem diese Anfrage ankam.
     *
     * Gegen den Host der Anfrage und nicht gegen config('app.url'): dieselbe
     * Anwendung antwortet auf metager.de, metager.org, metager3.de und einer
     * .onion-Adresse, und wer über eine davon hereinkommt, soll auf ihr bleiben.
     */
    private function isOurs(Request $request, string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null || $host === false) {
            // Ein reiner Pfad. Der ist unserer.
            return str_starts_with($url, "/") && !str_starts_with($url, "//");
        }

        $port = parse_url($url, PHP_URL_PORT);

        return ($host . ($port === null ? "" : ":" . $port)) === $request->getHttpHost();
    }

    /**
     * Ob dieses Formular von unserer eigenen Seite abgeschickt wurde.
     *
     * `Origin` ist die Antwort, wenn sie da ist: bei einem seitenübergreifenden
     * Formular schickt der Browser sie mit, und sie ist dann eine fremde.
     * Fehlt sie, entscheidet `Sec-Fetch-Site`; `none` heißt „direkt eingegeben“
     * und ist ebenfalls in Ordnung.
     *
     * Fehlen beide, wird durchgelassen. Das ist kein Loch, das jemand von einer
     * fremden Seite aus aufmachen kann — genau diese Kopfzeilen sind die, die
     * ein Browser nicht unterdrücken lässt. Es ist die Nachsicht gegenüber
     * einem sehr alten Browser, der beide nicht kennt.
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

    /** Gezählt wird pro Adresse; einen Benutzer gibt es hier ja noch nicht. */
    private function attemptKey(Request $request): string
    {
        return "login:" . $request->ip();
    }

    /**
     * Wohin der Erweiterungs-Knopf zeigt und wie er heißt.
     *
     * Die alte Seite bot drei Knöpfe mit drei Markenlogos an, von denen für
     * jeden Besucher höchstens einer je funktioniert hat. App\Support\Browser
     * weiß, in welchem Browser die Seite gerade steht, also ist es einer — und
     * dann braucht es auch keine fremden Logos in public/img.
     *
     * Ohne erkannten Browser mit Erweiterung führt der Knopf auf MetaGers
     * eigene Plugin-Seite: die beantwortet die Frage für jeden Browser und ist
     * die einzige ehrliche Antwort, wenn wir den Browser nicht kennen. Safari
     * und der Internet Explorer landen deshalb ebenfalls dort — für sie gibt es
     * keine Erweiterung, und ein Store-Link, der ins Leere führt, wäre
     * schlechter als eine Seite, die das erklärt.
     *
     * @return array{url: string, label: string}
     */
    private function extension(): array
    {
        $store = match (app(Browser::class)->name()) {
            "Firefox" => "https://addons.mozilla.org/firefox/addon/metager-suche/",
            "Chrome" => "https://chromewebstore.google.com/detail/metager-suche/gjfllojpkdnjaiaokblkmjlebiagbphd",
            "Edge" => "https://microsoftedge.microsoft.com/addons/detail/metager-suche/fdckbcmhkcoohciclcedgjmchbdeijog",
            default => null,
        };

        if ($store === null) {
            return ["url" => route("plugin"), "label" => trans("login.extension.install_generic")];
        }

        return [
            "url" => $store,
            "label" => trans("login.extension.install", ["browser" => app(Browser::class)->name()]),
        ];
    }

    /**
     * Zurück auf diese Seite nach einem abgewiesenen Versuch.
     *
     * Alles, was einen zweiten Versuch überstehen muss, steht wieder in der
     * Query — sonst verliert genau der Besucher die Callback-Marker der App
     * und sein Rückkehrziel, der sich beim ersten Mal vertippt hat.
     *
     * 303 und nicht 302: das Ziel ist ein Formular, und der Browser soll es
     * mit GET holen statt den fehlgeschlagenen Versuch zu wiederholen. Ohne
     * Zwischenspeicher, weil in der Query die Eingabe des Besuchers steht.
     *
     * @param array<string, string> $callback
     */
    private function back(
        array $callback,
        ?string $redirectSuccess,
        string $error,
        string $entered = ""
    ): RedirectResponse {
        $query = $callback;

        if ($redirectSuccess !== null) {
            $query["redirect_success"] = $redirectSuccess;
        }

        $query["key_error"] = $error;

        $entered = trim($entered);
        if ($entered !== "") {
            $query["invalid_key"] = mb_substr($entered, 0, self::MAX_PREFILL);
        }

        return redirect()
            ->to(route("login", $query), 303)
            ->header("Cache-Control", "no-store, private");
    }
}
