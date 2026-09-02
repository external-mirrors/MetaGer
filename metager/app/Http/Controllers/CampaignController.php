<?php

namespace App\Http\Controllers;

use App\Authentication\CampaignIssuer;
use App\Authentication\KeyUser;
use App\Http\Controllers\Concerns\HandlesKeyCheckout;
use App\Support\AppHosts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;

/**
 * Gutscheinaktionen — /konto/gutscheinaktionen, ohne Schlüssel im Pfad.
 *
 * Aus dem Keymanager (`/keys/key/<uuid>/campaigns`) hierher gezogen, im
 * selben Schnitt wie {@see OrderController}: was drüben blieb, ist die API
 * ({@see CampaignIssuer}); hier steht die Seite. Eine Liste plus ein
 * Anlegeformular, wie `views/campaign/manage.ejs` es zeigte — keine eigene
 * Detailseite pro Kampagne, es gibt nichts, das eine bräuchte.
 *
 * **Wem eine Kampagne gehört, prüft ausschließlich der Keyserver.** Anders
 * als bei Bestellungen liefert die Kampagnen-API keinen Schlüssel im
 * Antwortkörper zurück, der sich hier vergleichen ließe — `:id` steht immer
 * neben `$key` im Pfad, und eine fremde Kampagne ist dort eine 404, nie ein
 * Datensatz, den diese Seite je zu sehen bekäme. Dieselbe Vertrauensgrenze
 * wie bei jedem `/key/:key/...`-Schreibzugriff: MetaGer hat `$key` bereits
 * gegen das Cookie geprüft, bevor es hier ankommt.
 *
 * **Nicht hier:** die OIDC-geschützte Admin-Oberfläche bleibt auf dem
 * Keymanager. Der öffentliche Einlöseweg ist es nicht mehr —
 * {@see \App\Http\Controllers\VoucherController} hat ihn, unter `/c`.
 */
final class CampaignController extends Controller
{
    use HandlesKeyCheckout;

    public function index(Request $request, CampaignIssuer $issuer): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->resolveKey($request, route("account.campaigns"));
        if ($redirect !== null) {
            return $redirect;
        }

        $data = $issuer->list($key);

        return $this->render("campaigns.index", $key, [
            "campaigns" => $this->withPublicLinks($data["campaigns"] ?? [], AppHosts::shareableOrigin($request)),
            "maxCampaignVolume" => $data["max_campaign_volume"] ?? 0,
            "fields" => $this->emptyFields(),
            "errorCode" => null,
            "unreachable" => $data === null,
        ]);
    }

    public function store(Request $request, CampaignIssuer $issuer): Response|RedirectResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [, $key, $redirect] = $this->resolveKey($request, route("account.campaigns"));
        if ($redirect !== null) {
            return $redirect;
        }

        $fields = [
            "name" => trim((string) $request->input("name", "")),
            "tokens_per_key" => trim((string) $request->input("tokens_per_key", "")),
            "total_volume" => trim((string) $request->input("total_volume", "")),
        ];
        $voucherCount = trim((string) $request->input("voucher_count", ""));
        $payload = $voucherCount === "" ? $fields : array_merge($fields, ["voucher_count" => $voucherCount]);

        $result = $issuer->create($key, $payload);

        if ($result["ok"]) {
            return redirect()
                ->to(route("account.campaigns"))
                ->header("Cache-Control", "no-store, private");
        }

        $data = $issuer->list($key);

        return $this->render("campaigns.index", $key, [
            "campaigns" => $this->withPublicLinks($data["campaigns"] ?? [], AppHosts::shareableOrigin($request)),
            "maxCampaignVolume" => $data["max_campaign_volume"] ?? 0,
            "fields" => array_merge($fields, ["voucher_count" => $voucherCount]),
            "errorCode" => $result["code"],
            "unreachable" => false,
        ]);
    }

    public function disable(Request $request, int $id, CampaignIssuer $issuer): RedirectResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [, $key, $redirect] = $this->resolveKey($request, route("account.campaigns"));
        if ($redirect !== null) {
            return $redirect;
        }

        $issuer->disable($key, $id);

        return redirect()
            ->to(route("account.campaigns"))
            ->header("Cache-Control", "no-store, private");
    }

    public function destroy(Request $request, int $id, CampaignIssuer $issuer): RedirectResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [, $key, $redirect] = $this->resolveKey($request, route("account.campaigns"));
        if ($redirect !== null) {
            return $redirect;
        }

        $issuer->delete($key, $id);

        return redirect()
            ->to(route("account.campaigns"))
            ->header("Cache-Control", "no-store, private");
    }

    public function cardsPdf(Request $request, int $id, CampaignIssuer $issuer): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->resolveKey($request, route("account.campaigns"));
        if ($redirect !== null) {
            return $redirect;
        }

        $pdf = $issuer->cardsPdf($key, $id);
        if ($pdf === null) {
            abort(404);
        }

        return response($pdf["body"], 200)
            ->header("Content-Type", $pdf["content_type"])
            ->header("Content-Disposition", 'inline; filename="campaign-' . $id . '-cards.pdf"')
            ->header("Cache-Control", "no-store, private");
    }

    /** @return array<string, string> */
    private function emptyFields(): array
    {
        return ["name" => "", "tokens_per_key" => "", "total_volume" => "", "voucher_count" => ""];
    }

    /**
     * Adds each campaign's public redemption link — the one thing on this page
     * that is meant to leave it, pasted into a chat or an email for somebody
     * else to open. Two things it must not pick up on the way out:
     *
     * **The visitor's locale.** So not `url()`/`route()`:
     * `AppServiceProvider`'s `URL::formatPathUsing` hook stamps the current
     * request's `/{locale}` prefix on every path those generate, and the
     * recipient gets their own locale from `ResolveLocale` anyway.
     *
     * **The keymanager's internal address.** This used to read
     * `config("app.url")`, which is not the public address of anything:
     * `config("metager.metager.keymanager.server")` defaults to
     * `app.url . "/keys"`, so `app.url` is where *this application* reaches the
     * keyserver — `http://nginx:8080` in the compose stack. Every link the page
     * handed out pointed at a Docker-internal name. {@see AppHosts::shareableOrigin()}
     * is the origin the visitor is actually on instead, and carries the onion
     * exception with it.
     *
     * `/c/campaign/…` — not `route("voucher.campaign", …)` — for the same
     * reason `url()`/`route()` are avoided above: the recipient's locale is
     * not the creator's, and a MetaGer route name still runs through
     * `URL::formatPathUsing` regardless of `$origin`.
     *
     * @param list<array<string, mixed>> $campaigns
     * @return list<array<string, mixed>>
     */
    private function withPublicLinks(array $campaigns, string $origin): array
    {
        $base = $origin . "/c/campaign/";

        return array_map(
            fn (array $campaign) => $campaign + ["public_link" => $base . urlencode((string) $campaign["public_token"])],
            $campaigns,
        );
    }

    /**
     * Gemeinsamer Rahmen für die gerenderte Seite: Titel, Assets, wer zahlt.
     * Wortgleich zu OrderController::render() — dieselben zwei Stylesheets,
     * dieselbe Kontokachel.
     *
     * @param array<string, mixed> $extra
     */
    private function render(string $view, string $key, array $extra): Response
    {
        /** @var KeyUser $user */
        $user = Auth::guard("key")->user();

        return response()
            ->view($view, array_merge([
                "title" => trans("titles.campaigns"),
                "navbarFocus" => "login",
                "css" => [
                    Vite::asset("resources/less/metager/pages/account.less"),
                    Vite::asset("resources/less/metager/pages/checkout.less"),
                ],
                "js" => [Vite::asset("resources/js/account.js")],
                "key" => $key,
                "fingerprint" => $user->getKeyFingerprint(),
                "accountUrl" => route("account"),
            ], $extra))
            ->header("Cache-Control", "no-store, private");
    }
}
