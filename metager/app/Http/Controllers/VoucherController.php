<?php

namespace App\Http\Controllers;

use App\Authentication\CampaignRedemption;
use App\Authentication\CookieSupport;
use App\Authentication\KeyBackup;
use App\Landing\KeymanagerLinks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;

/**
 * Einen Gutscheincode einlösen — /c, aus dem Keymanager (`/keys/c`) hierher
 * gezogen.
 *
 * Die letzte der besucherseitigen `/keys`-Seiten: eine Aktionskarte oder ein
 * Link trägt einen Code, der Code wird hier gegen einen frischen MetaGer-
 * Schlüssel mit festem Guthaben getauscht. Was drüben blieb, ist die API
 * über die Kampagnen-Tabellen ({@see CampaignRedemption}); die drei Seiten —
 * Codeeingabe, Vorschau, „hier ist dein Schlüssel“ — stehen hier.
 *
 * **`/c` und nicht `/gutschein`.** Jede andere umgezogene Route trägt einen
 * deutschen Pfad; dieser nicht, mit Absicht: `/c` ist kurz, weil er von einer
 * gedruckten Karte abgetippt wird. Der alte `/keys/c` leitet dauerhaft
 * hierher weiter (keymanager `app/CampaignRedirect.js`) — Karten, die es
 * schon gibt, funktionieren weiter; eine heute gedruckte trägt gleich die
 * Adresse, die bleibt.
 *
 * **Zwei Einlösewege, ein Ziel.** Ein einzeln geteilter/gedruckter Code
 * ({@see teaser()}, {@see redeem()}) zieht aus einem endlichen Vorrat; der
 * öffentliche Link einer Kampagne ({@see publicTeaser()},
 * {@see publicRedeem()}) prägt bei jedem Klick frisch. Ab der Vorschauseite
 * sehen beide gleich aus.
 *
 * **Die Bremse ist geteilt.** Der Keyserver sieht von seiner Seite nur einen
 * angemeldeten Aufrufer; welcher Browser fragt, weiß nur diese Seite. Also
 * zählt sie fehlgeschlagene Code-Nachschläge pro Adresse — wie
 * {@see LoginController} und {@see KeyCreationController} —, und nur die
 * kampagnenweite Bremse für den öffentlichen Link (nicht pro Besucher) bleibt
 * drüben.
 */
final class VoucherController extends Controller
{
    /**
     * Wie oft von einer Adresse aus ein Code danebengehen darf, bevor eine
     * Weile Ruhe ist. Wortgleich zum Keymanager (`campaigns.redeem_rate_limit`):
     * nur *fehlgeschlagene* Nachschläge zählen, ein Besucher mit einem gültigen
     * Code wird nie gebremst.
     */
    private const MAX_ATTEMPTS = 10;
    private const ATTEMPT_WINDOW_SECONDS = 3600;

    /** Die Codelänge des Keyservers (`campaigns.code_length`). */
    private const CODE_LENGTH = 10;

    /** Das Alphabet gespeicherter Codes — ohne I, L, O (mehrdeutig). */
    private const CODE_ALPHABET = "0123456789ABCDEFGHJKMNPQRSTVWXYZ";

    /** Die Codeeingabe. */
    public function enter(Request $request): Response|RedirectResponse
    {
        if (($redirect = $this->sendKeyHoldersHome($request)) !== null) {
            return $redirect;
        }

        return $this->enterPage(null, "");
    }

    /** „Diesen Code“ — normalisieren und auf die Vorschauseite schicken. */
    public function submit(Request $request): Response|RedirectResponse
    {
        if (($redirect = $this->sendKeyHoldersHome($request)) !== null) {
            return $redirect;
        }

        // Webrouten laufen ohne Session, es gibt also kein CSRF-Token. Ein
        // fremdes Formular, das hierher abschickt, kann kaum Schaden anrichten
        // — es löst höchstens einen fremden Gutschein ein —, aber die Prüfung
        // steht auf jeder anderen POST-Route dieses Umzugs, und eine Ausnahme
        // wäre die Zeile, die beim nächsten Lesen auffällt.
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        if (RateLimiter::tooManyAttempts($this->attemptKey($request), self::MAX_ATTEMPTS)) {
            return $this->enterPage("rate_limited", $this->enteredCode($request));
        }

        $code = self::normalizeCode($this->enteredCode($request));

        if ($code === null) {
            RateLimiter::hit($this->attemptKey($request), self::ATTEMPT_WINDOW_SECONDS);

            return $this->enterPage("invalid_code", $this->enteredCode($request));
        }

        return redirect()
            ->to(route("voucher.code", ["code" => $code]))
            ->header("Cache-Control", "no-store, private");
    }

    /** Die Vorschau für einen einzeln geteilten/gedruckten Code. */
    public function teaser(Request $request, string $code, CampaignRedemption $redemption): Response|RedirectResponse
    {
        return $this->showTeaser(
            $request,
            fn () => $redemption->teaserByCode($code),
            route("voucher.code", ["code" => $code]),
            penalizeUnknown: true,
        );
    }

    /** Das Einlösen eines einzeln geteilten/gedruckten Codes. */
    public function redeem(Request $request, string $code, CampaignRedemption $redemption): Response|RedirectResponse
    {
        return $this->doRedeem(
            $request,
            fn () => $redemption->redeemByCode($code),
            penalizeUnknown: true,
        );
    }

    /** Die Vorschau für den öffentlichen Link einer Kampagne. */
    public function publicTeaser(Request $request, string $token, CampaignRedemption $redemption): Response|RedirectResponse
    {
        return $this->showTeaser(
            $request,
            fn () => $redemption->teaserByToken($token),
            route("voucher.campaign", ["token" => $token]),
            penalizeUnknown: false,
        );
    }

    /** Das Einlösen über den öffentlichen Link einer Kampagne. */
    public function publicRedeem(Request $request, string $token, CampaignRedemption $redemption): Response|RedirectResponse
    {
        return $this->doRedeem(
            $request,
            fn () => $redemption->redeemByToken($token),
            penalizeUnknown: false,
        );
    }

    /**
     * @param \Closure(): array $ask
     */
    private function showTeaser(Request $request, \Closure $ask, string $action, bool $penalizeUnknown): Response|RedirectResponse
    {
        if (($redirect = $this->sendKeyHoldersHome($request)) !== null) {
            return $redirect;
        }

        if (RateLimiter::tooManyAttempts($this->attemptKey($request), self::MAX_ATTEMPTS)) {
            return $this->errorPage("rate_limited");
        }

        $result = $ask();

        if ($result["ok"]) {
            return $this->page("voucher.teaser", [
                "title" => trans("titles.voucher"),
                "campaignName" => $result["campaign"]["name"],
                "tokens" => $result["campaign"]["tokens"],
                "validityDays" => $result["campaign"]["validity_days"],
                "action" => $action,
            ]);
        }

        if ($penalizeUnknown && $result["error"] === "invalid_code") {
            RateLimiter::hit($this->attemptKey($request), self::ATTEMPT_WINDOW_SECONDS);
        }

        return $this->errorPage($result["error"]);
    }

    /**
     * @param \Closure(): array $act
     */
    private function doRedeem(Request $request, \Closure $act, bool $penalizeUnknown): Response|RedirectResponse
    {
        if (($redirect = $this->sendKeyHoldersHome($request)) !== null) {
            return $redirect;
        }

        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        if (RateLimiter::tooManyAttempts($this->attemptKey($request), self::MAX_ATTEMPTS)) {
            return $this->errorPage("rate_limited");
        }

        $result = $act();

        if (!$result["ok"]) {
            if ($penalizeUnknown && $result["error"] === "invalid_code") {
                RateLimiter::hit($this->attemptKey($request), self::ATTEMPT_WINDOW_SECONDS);
            }

            return $this->errorPage($result["error"]);
        }

        $key = strtolower($result["key"]);

        // Das Cookie so, wie der Keymanager es gesetzt hat und wie
        // {@see KeyCreationController::submit()} es setzt: fünf Jahre, `lax`,
        // lesbar für Skripte (die Web-Erweiterung verfolgt es), unverschlüsselt
        // (`key` steht in EncryptCookies::$except, der Keymanager liest es unter
        // demselben Host).
        Cookie::queue(Cookie::forever("key", $key, "/", null, $request->isSecure(), false));

        $expiration = $result["expiration"] !== ""
            ? Carbon::parse($result["expiration"])->locale(app()->getLocale())->isoFormat("L")
            : "";

        return $this->page("voucher.redeemed", [
            "title" => trans("titles.voucher"),
            "tokens" => $result["charge"],
            "key" => $key,
            "fingerprint" => strtoupper(substr($key, -6)),
            "expiration" => $expiration,
            "qrUri" => KeyBackup::qrDataUri($key),
            "settingsUrl" => KeyBackup::settingsUrl($request, $key),
            // Das Cookie oben überlebt die Runde vielleicht nicht; `withKeyCheck`
            // trägt den Schlüssel deshalb auch auf diesen Weg, wie in
            // {@see KeyCreationController::submit()}. `settingsUrl` bringt sein
            // `?key=` schon selbst mit — der Hauptweg ist also ohnehin gedeckt.
            "accountUrl" => CookieSupport::withKeyCheck(route("account"), $key),
        ]);
    }

    private function enterPage(?string $error, string $oldCode): Response
    {
        // 200 für die leere Seite, 422 für einen falschen Code, 429 für die
        // Bremse — wie `POST /c` im Keymanager.
        $status = match ($error) {
            "invalid_code" => 422,
            "rate_limited" => 429,
            default => 200,
        };

        return $this->page("voucher.enter", [
            "title" => trans("titles.voucher"),
            "action" => route("voucher", [], false),
            "error" => $error,
            "oldCode" => $oldCode,
            "codeLength" => self::CODE_LENGTH,
        ], $status);
    }

    private function errorPage(string $error): Response
    {
        // „Nochmal versuchen“ nur, wenn ein zweiter Versuch etwas ändern kann.
        $retryable = !in_array($error, ["rate_limited", "unreachable"], true);

        return $this->page("voucher.error", [
            "title" => trans("titles.voucher"),
            "error" => $error,
            "retryUrl" => $retryable ? route("voucher") : null,
        ], self::statusFor($error));
    }

    /**
     * Gemeinsamer Rahmen für jede gerenderte Seite dieses Vorgangs.
     *
     * `no-store, private` auf allen: der Keymanager setzte es über ein
     * `router.use` am Kopf von `/c` auf jede Antwort, und die Geschwister
     * dieses Umzugs ({@see AccountController}, {@see OrderController},
     * {@see CampaignController}) tun es auch. Die Fehlerseite wiegt am
     * schwersten — sie zeigt den *pro-Adresse* gezählten Bremszustand, und ein
     * gemeinsamer Cache, der den an den nächsten Besucher weiterreicht, wäre
     * falsch.
     *
     * @param array<string, mixed> $data
     */
    private function page(string $view, array $data, int $status = 200): Response
    {
        return response()
            ->view($view, array_merge([
                "navbarFocus" => "login",
                "css" => [Vite::asset("resources/less/metager/pages/voucher.less")],
                "js" => [Vite::asset("resources/js/voucher.js")],
            ], $data), $status)
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Der HTTP-Status zu einem Fehlercode, so wie ihn der alte EJS-Router
     * setzte und wie ihn die Keymanager-API weiterreicht: 404 für einen Code
     * oder ein Token, das nichts ist; 410 für eins, das etwas war und
     * verbraucht ist; 429 für die Bremse; 502, wenn der Keyserver gar nicht
     * geantwortet hat. Übersetzungen für 404, 410 und 429 gibt es — dieser
     * Vorgang rendert dafür seine eigene Seite, aber der Status soll stimmen:
     * eine beendete Kampagne unter `/c/campaign/<token>` ist eine 410, damit
     * ein Crawler die tote Seite aus dem Index nimmt statt sie zu behalten.
     */
    private static function statusFor(string $error): int
    {
        return match ($error) {
            "invalid_code", "invalid_token" => 404,
            "already_redeemed", "campaign_inactive", "budget_exhausted" => 410,
            "rate_limited" => 429,
            "unreachable" => 502,
            default => 500,
        };
    }

    /**
     * Wer schon einen Schlüssel mitbringt, hat keinen Grund, einen weiteren
     * einzulösen — ab ins Konto. Dieselbe Reihenfolge (Query, Header, Cookie)
     * wie im {@see \App\Authentication\KeyAuthGuard} und wie in
     * {@see KeyCreationController::show()}; der Keymanager tat für `/c`
     * dasselbe.
     */
    private function sendKeyHoldersHome(Request $request): ?RedirectResponse
    {
        if (
            $request->filled("key")
            || $request->hasHeader("key")
            || $request->cookie("key") !== null
        ) {
            return redirect()
                ->to(KeymanagerLinks::accountForVisitor($request))
                ->header("Cache-Control", "no-store, private");
        }

        return null;
    }

    private function enteredCode(Request $request): string
    {
        $code = $request->input("code");

        return is_string($code) ? $code : "";
    }

    /**
     * Rohe Eingabe → gespeichertes Codeformat, oder null, wenn es keines sein
     * kann. Wortgleich zu `CampaignVoucher.NORMALIZE_CODE` im Keymanager:
     * Großbuchstaben, alles außer 0-9A-Z entfernt, die mehrdeutigen I/L → 1
     * und O → 0. Der Keyserver normalisiert selbst noch einmal — aber
     * {@see submit()} muss vorher wissen, ob es überhaupt auf die
     * Vorschauseite schicken soll.
     */
    private static function normalizeCode(string $input): ?string
    {
        $code = strtoupper($input);
        $code = preg_replace("/[^0-9A-Z]/", "", $code) ?? "";
        $code = strtr($code, ["I" => "1", "L" => "1", "O" => "0"]);

        if (strlen($code) !== self::CODE_LENGTH) {
            return null;
        }

        for ($i = 0; $i < strlen($code); $i++) {
            if (!str_contains(self::CODE_ALPHABET, $code[$i])) {
                return null;
            }
        }

        return $code;
    }

    private function attemptKey(Request $request): string
    {
        return "voucher:" . $request->ip();
    }

    /**
     * Ob dieses Formular von unserer eigenen Seite abgeschickt wurde.
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
