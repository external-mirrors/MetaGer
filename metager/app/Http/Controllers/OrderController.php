<?php

namespace App\Http\Controllers;

use App\Authentication\KeyUser;
use App\Authentication\OrderHistoryIssuer;
use App\Http\Controllers\Concerns\HandlesKeyCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Vite;

/**
 * Bestellungen und Rechnungen — /konto/bestellungen, ohne Schlüssel im Pfad.
 *
 * Aus dem Keymanager (`/keys/key/<uuid>/orders`) hierher gezogen, im selben
 * Schnitt wie {@see ChargeController}: was drüben blieb, ist die API, die die
 * Bestellung samt Zahlungen liefert ({@see OrderHistoryIssuer}) und die die
 * Auftragsbestätigung als PDF rendert — hier steht die Seite.
 *
 * **Keine Bestellliste.** Die alte Oberfläche hatte auch keine: man gibt eine
 * Zahlungs-ID ein und sieht *diese eine* Bestellung. {@see lookup()} zeigt das
 * Formular, {@see find()} nimmt die Eingabe entgegen und leitet auf
 * {@see show()} weiter — POST/redirect/GET, damit ein Neuladen die Suche nicht
 * wiederholt.
 *
 * **Wem die Bestellung gehört, entscheidet sich hier.** Die Nummer ist klein
 * und fortlaufend, kein Geheimnis; der Keyserver reicht den Schlüssel im
 * Antwortkörper mit, und jede Seite hier weist eine fremde Bestellung mit 404
 * ab, bevor irgendetwas davon angezeigt oder ein PDF gestreamt wird — dieselbe
 * Prüfung wie {@see ChargeController::returned()}.
 *
 * **Die Rechnung (InvoiceNinja) folgt in einem zweiten Schritt** — bis dahin
 * verlinkt {@see show()} sie noch nicht.
 */
final class OrderController extends Controller
{
    use HandlesKeyCheckout;

    /** Die öffentliche Nummer einer Bestellung: `Z<n>` oder die blanke Zahl. */
    private const REFERENCE = '/^Z?\d+$/';

    public function lookup(Request $request): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->resolveKey($request, route("account.orders"));
        if ($redirect !== null) {
            return $redirect;
        }

        $prefill = $request->query("reference", "");

        return $this->render("orders.lookup", $key, [
            "reference" => is_string($prefill) ? $prefill : "",
            "error" => null,
        ]);
    }

    public function find(Request $request, OrderHistoryIssuer $issuer): Response|RedirectResponse
    {
        if (!$this->sameOrigin($request)) {
            abort(403);
        }

        [, $key, $redirect] = $this->resolveKey($request, route("account.orders"));
        if ($redirect !== null) {
            return $redirect;
        }

        $reference = trim((string) $request->input("reference"));

        if (!preg_match(self::REFERENCE, $reference)) {
            return $this->render("orders.lookup", $key, [
                "reference" => $reference,
                "error" => "invalid",
            ]);
        }

        $order = $issuer->find($reference);

        // Eine fremde Bestellung ist hier dasselbe wie keine — die Antwort darf
        // nicht verraten, dass die Nummer zu einem anderen Schlüssel gehört.
        if ($order === null || $order["key"] !== $key) {
            return $this->render("orders.lookup", $key, [
                "reference" => $reference,
                "error" => "not_found",
            ]);
        }

        return redirect()
            ->to(route("account.orders.show", ["reference" => $order["public_id"]]))
            ->header("Cache-Control", "no-store, private");
    }

    public function show(Request $request, string $reference, OrderHistoryIssuer $issuer): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->resolveKey($request, route("account.orders.show", ["reference" => $reference]));
        if ($redirect !== null) {
            return $redirect;
        }

        if (!preg_match(self::REFERENCE, $reference)) {
            abort(404);
        }

        $order = $issuer->find($reference);

        if ($order === null || $order["key"] !== $key) {
            abort(404);
        }

        return $this->render("orders.show", $key, [
            "order" => $order,
            "confirmationUrl" => route("account.orders.confirmation", ["reference" => $order["public_id"]]),
        ]);
    }

    public function confirmation(Request $request, string $reference, OrderHistoryIssuer $issuer): Response|RedirectResponse
    {
        [, $key, $redirect] = $this->resolveKey($request, route("account.orders.show", ["reference" => $reference]));
        if ($redirect !== null) {
            return $redirect;
        }

        if (!preg_match(self::REFERENCE, $reference)) {
            abort(404);
        }

        // Erst die Zugehörigkeit über den JSON-Endpunkt prüfen — der PDF-
        // Endpunkt drüben liefert keinen Schlüssel, den wir vergleichen
        // könnten, und ein fremdes PDF darf diese Route nie streamen.
        $order = $issuer->find($reference);
        if ($order === null || $order["key"] !== $key) {
            abort(404);
        }

        $pdf = $issuer->confirmationPdf($order["public_id"], app()->getLocale());
        if ($pdf === null) {
            abort(404);
        }

        return response($pdf["body"], 200)
            ->header("Content-Type", $pdf["content_type"])
            ->header("Content-Disposition", 'inline; filename="' . $order["public_id"] . '.pdf"')
            ->header("Cache-Control", "no-store, private");
    }

    /**
     * Gemeinsamer Rahmen für die gerenderten Seiten: Titel, Assets, wer zahlt.
     *
     * @param array<string, mixed> $extra
     */
    private function render(string $view, string $key, array $extra): Response
    {
        /** @var KeyUser $user */
        $user = Auth::guard("key")->user();

        return response()
            ->view($view, array_merge([
                "title" => trans("titles.orders"),
                "navbarFocus" => "login",
                "css" => [
                    Vite::asset("resources/less/metager/pages/account.less"),
                    Vite::asset("resources/less/metager/pages/checkout.less"),
                ],
                "js" => [Vite::asset("resources/js/account.js")],
                "key" => $key,
                "fingerprint" => $user->getKeyFingerprint(),
                "accountUrl" => route("account"),
                "lookupUrl" => route("account.orders"),
            ], $extra))
            ->header("Cache-Control", "no-store, private");
    }
}
