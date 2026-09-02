<?php

use App\Landing\KeyPrice;
use Illuminate\Support\Facades\Vite;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AnonymousToken;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HealthcheckController;
use App\Http\Controllers\KeyCreationController;
use App\Http\Controllers\LangSelector;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MetaGerSearch;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Pictureproxy;
use App\Http\Controllers\Prometheus;
use App\Http\Controllers\SearchEngineList;
use App\Http\Controllers\SitesearchController;
use App\Http\Controllers\StartpageController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\TilesController;
use App\Http\Controllers\TTSController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ZitatController;
use App\Http\Middleware\AuthenticationValidation;
use App\Http\Middleware\LocalizationRedirect;
use App\Localization;
use App\Models\Authorization\Authorization;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class])->group(function () {

    Route::get("robots.txt", function (Request $request) {
        $responseData = "";
        if (App::environment("production")) {
            $responseData = view("robots.production");
        } else {
            $responseData = view("robots.development");
        }
        return response($responseData, 200, ["Content-Type" => "text/plain"]);
    });

    /**
     * The MetaGer Android app's App Link sign-in handback
     * (docs/10-open-decisions.md#d52 in the app-en repo): metager-keymanager
     * redirects a signed-in user back into the app through a verified
     * Android App Link, and Android checks this file against the app's
     * signing certificate before ever treating the link as belonging to it.
     *
     * A route rather than a static public file **on purpose**: `development`
     * is only ever a staging run of what `master` will serve, and a static
     * file cannot express "the same content everywhere except this one
     * trusted extra certificate in non-production" without the two branches
     * permanently disagreeing on tracked file content. This is the same
     * `App::environment("production")` check `robots.txt` above already
     * uses, so the code is identical on every branch and only the deployed
     * environment (APP_ENV, .gitlab-ci.yml) decides what gets served.
     *
     * Every debuggable build type of the app signs with one committed, and
     * therefore public, debug keystore. Its fingerprint is safe to publish,
     * but only where a build carrying it could ever legitimately ask to be
     * trusted — that must never include production: anyone can clone the
     * app and sign it with that same public key, so trusting it here would
     * let a sideloaded clone receive a real user's key on first sign-in.
     * Real release builds never even ask metager3.de, so serving their
     * fingerprints there too is harmless rather than useful — kept for
     * uniformity, not because anything depends on it.
     */
    Route::get(".well-known/assetlinks.json", function () {
        $releaseFingerprint = "7F:85:CC:0C:5A:4D:CD:6C:3E:BF:9C:D2:C2:4F:51:48:34:42:00:99:57:0F:80:14:19:DE:3C:C6:3B:88:67:F9";
        $fdroidFingerprint = "1E:75:2E:3A:CD:C9:9D:7A:AE:AA:EE:39:CC:61:0D:41:24:76:EC:D7:98:0A:18:5F:B6:65:33:E4:A1:AB:9B:67";
        // Every debuggable build type shares this one certificate
        // (android/app/debug.keystore, committed to the app-en repo).
        $debugFingerprint = "FA:C6:17:45:DC:09:03:78:6F:B9:ED:E6:2A:96:2B:39:9F:73:48:F0:BB:6F:89:9B:83:32:66:75:91:03:3B:9C";

        $packages = [
            "de.metager.metagerapp" => $releaseFingerprint,
            "de.metager.metagerapp.manual" => $releaseFingerprint,
            "de.metager.metagerapp.fdroid" => $fdroidFingerprint,
        ];

        /**
         * The app's snapshot build — app-en's `debug_playstore`, which carries
         * `applicationIdSuffix '.snapshot'` so a developer can keep a Play
         * install on the same phone (app-en docs/08, "Testing a real device
         * without Metro"). Being a debuggable build type is all it ever is:
         * there is no release counterpart and therefore no release
         * fingerprint to pair with, so the debug certificate is its only one.
         *
         * Added inside this check rather than to the list above, because the
         * unconditional version is the one dangerous way to write this entry:
         * it would publish a statement on metager.de trusting the committed,
         * public debug key, which is exactly the "a sideloaded clone receives
         * a real user's key" hole the comment above exists to keep shut. The
         * other three packages can be listed unconditionally precisely
         * because each has a real, private signing key of its own.
         */
        if (!App::environment("production")) {
            $packages["de.metager.metagerapp.snapshot"] = $debugFingerprint;
        }

        $statements = [];
        foreach ($packages as $packageName => $fingerprint) {
            $fingerprints = [$fingerprint];
            if (!App::environment("production") && $fingerprint !== $debugFingerprint) {
                $fingerprints[] = $debugFingerprint;
            }
            $statements[] = [
                "relation" => ["delegate_permission/common.handle_all_urls"],
                "target" => [
                    "namespace" => "android_app",
                    "package_name" => $packageName,
                    "sha256_cert_fingerprints" => $fingerprints,
                ],
            ];
        }

        return response()->json($statements);
    });

    /** ADD ALL LOCALIZED ROUTES INSIDE THIS GROUP **/

    Route::get('/', [StartpageController::class, "loadStartPage"])->name("startpage");
    Route::post('authorized', [StartpageController::class, "isLoggedIn"])->name("startpage:loggedin");

    Route::get('tts', [TTSController::class, 'tts'])->name("tts");

    Route::get('impressum', function () {
        return view('impressum')
            ->with('title', trans('titles.impressum'))
            ->with('navbarFocus', 'kontakt');
    })->name('impress');
    Route::get('impressum.html', function () {
        return redirect(url('impressum'));
    });

    Route::group(['prefix' => 'suggest'], function () {
        Route::get("cost", [SuggestionController::class, 'tokenCost'])->name("suggest_cost");
        Route::get("cancel", [SuggestionController::class, "cancelSuggest"])->name("suggest_cancel");
        Route::any("{key?}", [SuggestionController::class, "suggest"])->name("suggest");
    });

    Route::group(['prefix' => 'api/event'], function () {
        Route::post("key/login", [EventController::class, "loginEvent"])->name("event_key_login");
        Route::post("key/update", [EventController::class, "keyUpdateEvent"])->name("event_key_update");
    });


    Route::get('about', function () {
        return view('about')
            ->with('title', trans('titles.about'))
            ->with('navbarFocus', 'info');
    });
    Route::get('team', function () {
        return view('team.team')
            ->with('title', trans('titles.team'))
            ->with('navbarFocus', 'kontakt');
    });
    Route::get('team/pubkey-wsb', function () {
        return view('team.pubkey-wsb')
            ->with('title', trans('titles.team'))
            ->with('navbarFocus', 'kontakt');
    });

    Route::get('kontakt/{url?}', function ($url = "") {
        $to_mail = Localization::getLanguage() === "de" ? config("metager.metager.ticketsystem.germanmail") : config("metager.metager.ticketsystem.englishmail");
        return view('kontakt.kontakt')
            ->with('title', trans('titles.kontakt'))
            ->with('to_mail', $to_mail)
            ->with('navbarFocus', 'kontakt')
            ->with('url', $url)
            ->with('js', [Vite::asset('resources/js/contact.js')])
            ->with("css", [Vite::asset('resources/less/metager/pages/contact.less')]);
    })->name("contact");

    //Route::post('kontakt', [MailController::class, 'contactMail']);
    Route::get('adblocker', function () {
        return response(view('adblocker', ["title" => __("titles.adblocker"), 'css' => [Vite::asset('resources/less/metager/pages/adblocker.less')]]));
    })->name("adblocker");

    Route::group(["prefix" => "membership"], function () {
        Route::get("token", [MembershipController::class, "getToken"]);
        Route::get("paypal/authorized/{application_id}", [MembershipController::class, "paypalHandleAuthorized"])->name("membership_paypal_authorized");
        Route::get("paypal/cancelled/{application_id}", [MembershipController::class, "paypalHandleCancelled"])->name("membership_paypal_cancelled");
        Route::post("webhook/paypal", [MembershipController::class, "paypalWebhook"]);
        Route::get("/success/{application_id?}", [MembershipController::class, "success"])->name("membership_success");
        Route::get("/{application_id?}", [MembershipController::class, "contactData"])->name("membership_form");
        Route::post("/{application_id?}", [MembershipController::class, "submitMembershipForm"]);
        Route::get("/{application_id}/abort", [MembershipController::class, "abortApplication"])->name("membership_abort");
    });

    Route::get('tor', function () {
        return view('tor')
            ->with('title', 'tor hidden service - MetaGer')
            ->with('navbarFocus', 'dienste');
    });

    Route::group(['prefix' => 'spende'], function () {
        Route::get('/', [DonationController::class, "amount"])->name("spende");
        Route::get('/qr', [DonationController::class, "amountQr"]);
        Route::get('/{amount}', [DonationController::class, "interval"]);
        Route::get('/{amount}/{interval}', [DonationController::class, "paymentMethod"]);
        Route::get('/{amount}/{interval}/{funding_source}/{timestamp}/finished', [DonationController::class, "donationFinished"])->name("thankyou");
        Route::get('/{amount}/{interval}/banktransfer', [DonationController::class, 'banktransfer']);
        Route::get('/{amount}/{interval}/directdebit', [DonationController::class, 'directdebit']);
        Route::post('/{amount}/{interval}/directdebit', [DonationController::class, 'directdebitExecute']);
        Route::get('/{amount}/{interval}/banktransfer/qr', [DonationController::class, 'banktransferQr']);
        Route::get('/{amount}/{interval}/paypal/{funding_source}', [DonationController::class, 'paypalPayment'])->name("paypalPayment");
        Route::get('/{amount}/{interval}/paypal/{funding_source}/order', [DonationController::class, 'paypalCreateOrder']);
        Route::post('/{amount}/{interval}/paypal/{funding_source}/order', [DonationController::class, 'paypalCaptureOrder']);
        Route::post('/{amount}/{interval}/paypal/{funding_source}/subscription', [DonationController::class, 'paypalCreateSubscription'])->name("paypal-subscription");
    });

    Route::get('beitritt', function () {
        if (Localization::getLanguage() === "de") {
            return response()->download(storage_path('app/public/aufnahmeantrag-de.pdf'), "SUMA-EV_Beitrittsformular_" . (new \DateTime())->format("Y_m_d") . ".pdf", ["Content-Type" => "application/pdf"]);
        } else {
            return response()->download(storage_path('app/public/aufnahmeantrag-en.pdf'), "SUMA-EV_Membershipform_" . (new \DateTime())->format("Y_m_d") . ".pdf", ["Content-Type" => "application/pdf"]);
        }
    })->name("beitritt");

    Route::get('bform1.htm', function () {
        return redirect('beitritt');
    });



    Route::get('datenschutz', function () {
        return view('privacy')
            ->with('css', [Vite::asset('resources/less/metager/pages/privacy.less')])
            ->with('navbarFocus', 'datenschutz');
    });

    Route::get('transparency', function () {
        return view('transparency')
            ->with('title', trans('titles.transparency'))
            ->with('navbarFocus', 'info');
    })->name('transparency');

    /**
     * Die Seiten, die aus dem Keymanager hierher gezogen sind.
     *
     * /preise, /agb und die beiden Hilfeseiten lagen unter /keys; der
     * Keymanager leitet die alten Pfade dauerhaft hierher weiter
     * (pass/app/StaticPageRedirects.js). Namen tragen sie, weil der
     * Bezahlvorgang im anderen Repository und mehrere Blades hier auf sie
     * verlinken — App\Landing\KeymanagerLinks ist danach nur noch für den
     * Schlüsselvorgang zuständig.
     */
    Route::get('preise', function () {
        return view('price')
            ->with('title', trans('titles.price'))
            ->with('navbarFocus', 'info')
            ->with('tiers', KeyPrice::tiers())
            ->with('linkApp', LaravelLocalization::getLocalizedURL(null, "/app"))
            ->with('linkToken', route('anonymous-token'))
            ->with('css', [Vite::asset('resources/less/metager/pages/price.less')]);
    })->name('price');

    Route::get('agb', function () {
        return view('agb')
            ->with('title', trans('titles.agb'))
            ->with('navbarFocus', 'info')
            ->with('css', [Vite::asset('resources/less/metager/pages/agb.less')]);
    })->name('agb');

    Route::get('hilfe/schluessel', function () {
        return view('help.key')
            ->with('title', trans('titles.help-key'))
            ->with('navbarFocus', 'hilfe');
    })->name('key-faq');

    Route::get('hilfe/anonyme-token', function () {
        return view('help.anonymous-token')
            ->with('title', trans('titles.anonymous-token'))
            ->with('navbarFocus', 'hilfe');
    })->name('anonymous-token');

    /**
     * Die Anmeldung, aus /keys/key/enter hierher gezogen — Seite und Vorgang.
     *
     * Steht bei den anderen umgezogenen Seiten, ist aber die einzige, die nicht
     * nur Text ist. Was beim Keymanager blieb, ist eine einzige Frage über die
     * API: was eine Eingabe ist (App\Authentication\KeyResolver).
     *
     * Dieselbe Adresse für beide Richtungen, damit das Formular auf sich selbst
     * zeigt und ein Fehlversuch ohne zweiten URL zurückfindet.
     *
     * `login` als Name ist der, auf den Laravels Authenticate-Middleware ohne
     * weiteres Zutun zeigt. Das ist hier richtig und trotzdem eine Notiz wert:
     * die Logs-API hat ihre eigene Anmeldung unter `logs:login`, und die beiden
     * haben nichts miteinander zu tun.
     */
    Route::get('anmelden', [LoginController::class, 'show'])->name('login');
    Route::post('anmelden', [LoginController::class, 'submit'])->name('login.submit');

    /**
     * Das Erstellen eines Schlüssels, aus /keys/key/create hierher gezogen.
     *
     * Der Zwilling der Anmeldung, und derselbe Schnitt: beim Keyserver blieb
     * eine UUID, die noch niemandem gehört (App\Authentication\KeyIssuer),
     * alles andere steht hier. Anders als bei /keys/key/enter ist der alte Pfad
     * vollständig weitergeleitet — auch der Zweig für einen bereits
     * angemeldeten Besucher, weil diese Seite die Fallunterscheidung selbst
     * trifft.
     *
     * `key-create` und nicht `create`: der Name steht neben `key-faq` und
     * `anonymous-token` in derselben Tabelle, und was hier erstellt wird, ist
     * genau eines.
     */
    Route::get('schluessel-erstellen', [KeyCreationController::class, 'show'])->name('key-create');
    Route::post('schluessel-erstellen', [KeyCreationController::class, 'submit'])->name('key-create.submit');

    /**
     * Das Konto, aus /keys/key/<uuid> hierher gezogen.
     *
     * Die dritte Seite des Schlüsselvorgangs und die, bei der der Umzug am
     * meisten einbringt: die alte Adresse trug den Schlüssel im *Pfad*, also in
     * der Verlaufsliste, im Referer jeder von dort verlinkten Seite und in
     * jedem Bildschirmfoto einer Supportanfrage. Diese hier trägt nichts — wer
     * gemeint ist, steht im Cookie (App\Authentication\KeyAuthGuard).
     *
     * `konto` und nicht `dashboard`: es ist die Seite, auf die die Kontokachel
     * in der Ecke zeigt, und die heißt in jeder Sprache nach dem Konto.
     */
    Route::get('konto', [AccountController::class, 'show'])->name('account');

    /**
     * Der Anmeldecode für den Übertragen-Dialog, im Sekundentakt abgefragt.
     *
     * Unter /konto und nicht unter /api, weil er nur dort einen Sinn hat und
     * weil er den angemeldeten Besucher voraussetzt: welcher Schlüssel gemeint
     * ist, steht im Cookie. Die entsprechende Route im Keymanager hatte ihn im
     * Pfad.
     */
    Route::get('konto/anmeldecode', [AccountController::class, 'loginCode'])->name('account.logincode');

    /**
     * Aufladen, der zweite Schritt des Bezahlvorgangs, der aus dem Keymanager
     * hierher zieht — App\Http\Controllers\ChargeController hat den Grund.
     * Wie bei /konto trägt keine dieser Adressen den Schlüssel; er kommt aus
     * dem Cookie.
     *
     * `{amount}` ist auf Ziffern beschränkt, nicht weil eine andere Eingabe
     * hier gefährlich wäre — der Controller prüft ohnehin gegen
     * App\Landing\KeyPrice::tiers() —, sondern damit ein nicht-numerischer
     * Wert eine 404 statt einer 400 ist: die Adresse existiert dann schlicht
     * nicht, so wie /konto/aufladen/aci4T87k7DmMXVLmJ auch für einen
     * type-juggling-Versuch keine Auskunft geben soll.
     *
     * `cash`, die drei Micropayment-Zahlweisen, Wero (vrpayment), die sieben
     * PayPal-Zahlweisen (SDK-getrieben statt eines einfachen POST-Formulars —
     * siehe App\Authentication\PayPalChargeIssuer) und die Entwicklungs-
     * Zahlungsart (nur unter app()->environment('local') erreichbar) laufen
     * inzwischen alle hier; App\Landing\KeymanagerLinks::checkout() bleibt
     * nur noch als Infrastruktur für Zahlarten stehen, die künftig
     * dazukommen.
     *
     * Keine eigene Wahl-Seite mehr pro Anbieter: `show()` verlinkt direkt auf
     * jede einzelne Zahlweise, PayPal und Micropayment eingeschlossen. Wer
     * bezahlen will, sucht eine Zahlweise, die er kennt — "Micropayment" oder
     * "PayPal" als Zwischenschritt sagt ihm das nicht. `account.checkout.
     * micropayment` und `account.checkout.paypal` (ohne fundingSource/service)
     * gibt es deshalb nicht mehr; alles, was früher dorthin zurückführte
     * (ungültige Auswahl, nicht erreichbarer Keyserver, "zurück"), führt jetzt
     * zu `show()` selbst.
     */
    Route::get('konto/aufladen/{amount}', [ChargeController::class, 'show'])
        ->whereNumber('amount')
        ->name('account.checkout');
    Route::get('konto/aufladen/{amount}/bar', [ChargeController::class, 'cashShow'])
        ->whereNumber('amount')
        ->name('account.checkout.cash');
    Route::post('konto/aufladen/{amount}/bar', [ChargeController::class, 'cashSubmit'])
        ->whereNumber('amount')
        ->name('account.checkout.cash.submit');
    Route::get('konto/aufladen/{amount}/bar/{reference}', [ChargeController::class, 'cashCreated'])
        ->whereNumber('amount')
        ->name('account.checkout.cash.created');
    Route::get('konto/aufladen/{amount}/entwicklung', [ChargeController::class, 'manualShow'])
        ->whereNumber('amount')
        ->name('account.checkout.manual');
    Route::post('konto/aufladen/{amount}/entwicklung', [ChargeController::class, 'manualSubmit'])
        ->whereNumber('amount')
        ->name('account.checkout.manual.submit');
    Route::get('konto/aufladen/{amount}/micropayment/{service}', [ChargeController::class, 'micropaymentServiceShow'])
        ->whereNumber('amount')
        ->name('account.checkout.micropayment.service');
    Route::post('konto/aufladen/{amount}/micropayment/{service}', [ChargeController::class, 'micropaymentSubmit'])
        ->whereNumber('amount')
        ->name('account.checkout.micropayment.submit');
    Route::get('konto/aufladen/{amount}/vrpayment', [ChargeController::class, 'vrpaymentShow'])
        ->whereNumber('amount')
        ->name('account.checkout.vrpayment');
    Route::post('konto/aufladen/{amount}/vrpayment', [ChargeController::class, 'vrpaymentSubmit'])
        ->whereNumber('amount')
        ->name('account.checkout.vrpayment.submit');
    /**
     * PayPal — die einzige Zahlart, deren Seite vom SDK im Browser getrieben
     * wird statt von einem einfachen POST-Formular. Sieben Zahlweisen, jede
     * eine eigene Kachel auf `show()` statt hinter einer eigenen Wahl-Seite;
     * App\Authentication\PayPalChargeIssuer hat die drei Schritte
     * (Konfiguration, Anlegen, Einlösen), die resources/js/checkout-paypal.js
     * in dieser Reihenfolge aufruft.
     */
    Route::get('konto/aufladen/{amount}/paypal/{fundingSource}', [ChargeController::class, 'paypalServiceShow'])
        ->whereNumber('amount')
        ->where('fundingSource', 'paypal|card|p24|bancontact|blik|eps|mybank')
        ->name('account.checkout.paypal.service');
    Route::post('konto/aufladen/{amount}/paypal/{fundingSource}/order/create', [ChargeController::class, 'paypalOrderCreate'])
        ->whereNumber('amount')
        ->where('fundingSource', 'paypal|card|p24|bancontact|blik|eps|mybank')
        ->name('account.checkout.paypal.order.create');
    Route::post('konto/aufladen/{amount}/paypal/{fundingSource}/order/capture', [ChargeController::class, 'paypalOrderCapture'])
        ->whereNumber('amount')
        ->where('fundingSource', 'paypal|card|p24|bancontact|blik|eps|mybank')
        ->name('account.checkout.paypal.order.capture');
    /**
     * Die Rückkehr von einer weiterleitenden Zahlungsart — bewusst ohne
     * {amount}, die öffentliche Nummer der Ladung reicht, um sie
     * nachzuschlagen. Micropayment und VR Payment kommen hierher zurück;
     * PayPal braucht sie nicht — sein Rückweg bleibt im eigenen Ursprung,
     * die "capture"-Antwort trägt dieselbe Adresse nur als JSON statt als
     * Weiterleitung.
     */
    Route::get('konto/aufladen/abschluss/{reference}', [ChargeController::class, 'returned'])
        ->name('account.checkout.returned');

    /**
     * Bestellungen und Rechnungen, aus dem Keymanager (`/keys/key/<uuid>/orders`)
     * hierher gezogen — App\Http\Controllers\OrderController hat den Grund.
     * Kein {amount} und kein Schlüssel im Pfad: eine Bestellung wird über ihre
     * öffentliche Nummer nachgeschlagen, und wem sie gehört, prüft der
     * Controller gegen den Cookie-Schlüssel.
     *
     * `bestellungen` und nicht unter `aufladen`: das Aufladen ist der Kauf und
     * endet, wenn die Zahlung ankommt; das hier ist der Blick zurück auf eine
     * abgeschlossene Bestellung. Es ist die Seite, auf die die Kontokachel
     * „Bestellungen und Rechnungen" zeigt.
     */
    Route::get('konto/bestellungen', [OrderController::class, 'lookup'])
        ->name('account.orders');
    Route::post('konto/bestellungen', [OrderController::class, 'find'])
        ->name('account.orders.find');
    Route::get('konto/bestellungen/{reference}', [OrderController::class, 'show'])
        ->name('account.orders.show');
    Route::get('konto/bestellungen/{reference}/auftragsbestaetigung.pdf', [OrderController::class, 'confirmation'])
        ->name('account.orders.confirmation');
    Route::get('konto/bestellungen/{reference}/rechnung', [OrderController::class, 'invoice'])
        ->name('account.orders.invoice');
    Route::post('konto/bestellungen/{reference}/rechnung', [OrderController::class, 'invoiceRequest'])
        ->name('account.orders.invoice.request');
    Route::get('konto/bestellungen/{reference}/rechnung.pdf', [OrderController::class, 'invoiceDownload'])
        ->name('account.orders.invoice.pdf');
    Route::get('konto/bestellungen/{reference}/erstattung', [OrderController::class, 'refund'])
        ->name('account.orders.refund');
    Route::post('konto/bestellungen/{reference}/erstattung', [OrderController::class, 'refundRequest'])
        ->name('account.orders.refund.request');

    /**
     * Gutscheinaktionen, aus dem Keymanager (`/keys/key/<uuid>/campaigns`)
     * hierher gezogen — App\Http\Controllers\CampaignController hat den
     * Grund. Eine Liste plus ein Anlegeformular statt einer Detailseite pro
     * Kampagne; wem eine Kampagne gehört, prüft ausschließlich der Keyserver
     * gegen den Schlüssel im Pfad, nicht dieser Controller — anders als bei
     * Bestellungen gibt es hier keinen Schlüssel im Antwortkörper, den man
     * vergleichen könnte.
     */
    Route::get('konto/gutscheinaktionen', [CampaignController::class, 'index'])
        ->name('account.campaigns');
    Route::post('konto/gutscheinaktionen', [CampaignController::class, 'store'])
        ->name('account.campaigns.store');
    Route::post('konto/gutscheinaktionen/{id}/deaktivieren', [CampaignController::class, 'disable'])
        ->whereNumber('id')
        ->name('account.campaigns.disable');
    Route::post('konto/gutscheinaktionen/{id}/loeschen', [CampaignController::class, 'destroy'])
        ->whereNumber('id')
        ->name('account.campaigns.delete');
    Route::get('konto/gutscheinaktionen/{id}/karten.pdf', [CampaignController::class, 'cardsPdf'])
        ->whereNumber('id')
        ->name('account.campaigns.cards');

    /**
     * Einen Gutscheincode einlösen — aus dem Keymanager (`/keys/c`) hierher
     * gezogen, die letzte besucherseitige `/keys`-Seite. App\Http\Controllers\
     * VoucherController hat den Grund und die beiden Einlösewege.
     *
     * `/c` und nicht `/gutschein`: kurz, weil eine gedruckte Karte diesen Pfad
     * von Hand tippbar macht — anders als jede andere umgezogene Route hier,
     * die deutsch heißt. `/c` beim Keymanager leitet dauerhaft hierher weiter.
     *
     * `{code}` und `{token}` bekommen kein `->where()`: ein Code oder Token,
     * der nicht ins erwartete Format passt, ist eine gültige Anfrage an eine
     * echte Seite — sie endet in einer übersetzten Fehlermeldung
     * (`invalid_code`/`invalid_token`), nicht in einer nichtssagenden 404, wie
     * ein Tippfehler von einer Karte es sonst wäre.
     */
    Route::get('c', [VoucherController::class, 'enter'])->name('voucher');
    Route::post('c', [VoucherController::class, 'submit'])->name('voucher.submit');
    Route::get('c/campaign/{token}', [VoucherController::class, 'publicTeaser'])->name('voucher.campaign');
    Route::post('c/campaign/{token}', [VoucherController::class, 'publicRedeem'])->name('voucher.campaign.redeem');
    Route::get('c/{code}', [VoucherController::class, 'teaser'])->name('voucher.code');
    Route::post('c/{code}', [VoucherController::class, 'redeem'])->name('voucher.code.redeem');

    Route::get('search-engine', [SearchEngineList::class, 'index']);
    Route::get('hilfe', function () {
        return view('help/help')
            ->with('title', trans('titles.help'))
            ->with('navbarFocus', 'hilfe');
    });

    Route::get('hilfe/easy-language', function () {
        return view('help/easy-language/help')
            ->with('title', trans('titles.help'))
            ->with('navbarFocus', 'hilfe')
            ->with('css', [
                Vite::asset('resources/less/metager/pages/help-easy-language.less'),
            ]);
    });

    Route::get('hilfe/faktencheck', function () {
        return view('help/faktencheck')
            ->with('title', trans('titles.faktencheck'))
            ->with('navbarFocus', 'hilfe');
    })->name('faktencheck');

    Route::get('hilfe/hauptseiten', function () {
        return view('help/help-mainpages')
            ->with('title', trans('titles.help-mainpages'))
            ->with('navbarFocus', 'hilfe');
    })->name("help-mainpages");

    Route::get('hilfe/easy-language/mainpages', function () {
        return view('help/easy-language/help-mainpages')
            ->with('title', trans('titles.help-mainpages'))
            ->with('navbarFocus', 'hilfe')
            ->with('css', [
                Vite::asset('resources/less/metager/pages/help-easy-language.less'),
            ]);
    });

    Route::get('hilfe/funktionen', function () {
        return view('help/help-functions')
            ->with('title', trans('titles.help-functions'))
            ->with('navbarFocus', 'hilfe');
    });

    Route::get('hilfe/easy-language/functions', function () {
        return view('help/easy-language/help-functions')
            ->with('title', trans('titles.help-functions'))
            ->with('navbarFocus', 'hilfe')
            ->with('css', [
                Vite::asset('resources/less/metager/pages/help-easy-language.less'),
            ]);
    });

    Route::get('hilfe/dienste', function () {
        return view('help/help-services')
            ->with('title', trans('titles.help-services'))
            ->with('navbarFocus', 'hilfe');
    });

    Route::get('hilfe/easy-language/services', function () {
        return view('help/easy-language/help-services')
            ->with('title', trans('titles.help-services'))
            ->with('navbarFocus', 'hilfe')
            ->with('css', [
                Vite::asset('resources/less/metager/pages/help-easy-language.less'),
            ]);
    });

    Route::get('hilfe/datensicherheit', function () {
        return view('help/help-privacy-protection')
            ->with('title', trans('titles.help-privacy-protection'))
            ->with('navbarFocus', 'hilfe');
    });

    Route::get('hilfe/easy-language/privacy-protection', function () {
        return view('help/easy-language/help-privacy-protection')
            ->with('title', trans('titles.help-privacy-protection'))
            ->with('navbarFocus', 'hilfe')
            ->with('css', [
                Vite::asset('resources/less/metager/pages/help-easy-language.less'),
            ]);
    });

    Route::get('hilfe/easy-language/glossary', function () {
        // Check if a previous URL is given that we can offer a back button for
        $previous = request()->input("previous_url", URL::previous());

        $allowed_hosts = [
            "metager.de",
            "metager.org"
        ];

        $host = parse_url($previous, PHP_URL_HOST);
        $current_host = request()->getHost();
        $previous_url = null; // URL for the back button

        if (($host === $current_host || in_array($current_host, $allowed_hosts)) && preg_match("/^http(s)?:\/\//", $previous)) { // only if the host of that URL matches the current host
            $previous_url = $previous;
        }
        return view('help/easy-language/glossary')
            ->with('title', trans('titles.help-glossary'))
            ->with('navbarFocus', 'hilfe')
            ->with("previous_url", $previous_url)
            ->with('css', [
                Vite::asset('resources/less/metager/pages/help-easy-language.less'),
            ]);
    });

    Route::get('faq', function () {
        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/hilfe'));
    });

    Route::get('widget', function () {
        return view('widget.widget')
            ->with('title', trans('titles.widget'))
            ->with('navbarFocus', 'dienste');
    });

    Route::get('sitesearch', [SitesearchController::class, 'loadPage']);

    Route::get('websearch', function () {
        // Inlined into the snippet users paste onto their own site — content, not a URL.
        $css = Vite::content("resources/less/metager/pages/widget/widget-template.less");
        return view('widget.websearch')
            ->with('title', trans('titles.websearch'))
            ->with('navbarFocus', 'dienste')
            ->with('css', [Vite::asset('resources/less/metager/pages/widget/widget.less'), Vite::asset('resources/less/metager/pages/widget/widget-template.less')])
            ->with('template_preview', view('widget.websearch-template')->render())
            ->with('template_webpage', view('widget.websearch-template', ["css" => $css])->render());
    });

    Route::get('zitat-suche', [ZitatController::class, 'zitatSuche']);

    Route::get('jugendschutz', function () {
        return view('jugendschutz')
            ->with('title', trans('titles.jugendschutz'));
    });


    Route::get('prevention', function () {
        return view('prevention-information')
            ->with('title', trans('titles.prevention'))
            ->with('css', [Vite::asset('resources/less/metager/pages/prevention-information.less')]);
    });

    Route::get('age.xml', function () {
        $response = Response::make(file_get_contents(resource_path('age/age.xml')));
        $response->header('Content-Type', "application/xml");
        return $response;
    });
    Route::get('age-de.xml', function () {
        $response = Response::make(file_get_contents(resource_path('age/age-de.xml')));
        $response->header('Content-Type', "application/xml");
        return $response;
    });

    Route::get('plugin', function (Request $request) {
        return view('plugin-page')
            ->with('title', trans('titles.plugin'))
            ->with('navbarFocus', 'dienste')
            ->with('agent', $browser = \App\Support\Browser::fromRequest($request))
            ->with('request', $request->input('request', 'GET'))
            ->with('browser', $browser->name())
            ->with('css', [
                Vite::asset('resources/less/metager/pages/plugin-page.less'),
            ]);
    })->name("plugin");

    Route::get('coupon', function (Request $request) {
        return redirect(LaravelLocalization::getLocalizedURL(null, url("/keys/key/enter")));
    });

    Route::get('tiles', [TilesController::class, 'loadTakeTiles'])->name("tiles");

    Route::get('settings', function () {
        return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/'));
    });

    Route::get('/search', [MetaGerSearch::class, 'searchGoogle']);
    Route::match(['get', 'post'], 'meta/meta.ger3', [MetaGerSearch::class, 'search'])->middleware(['httpcache', AuthenticationValidation::class])->name("resultpage");
    Route::get('meta/loadMore', [MetaGerSearch::class, 'loadMore']);
    Route::get('anonymous-token/cost', [AnonymousToken::class, "cost"])->withoutMiddleware([LocalizationRedirect::class]);
    Route::post('anonymous-token', [AnonymousToken::class, "pay"])->withoutMiddleware([LocalizationRedirect::class]);

    Route::get('meta/picture', [Pictureproxy::class, 'get'])->name("imageproxy");

    Route::get('tips', [MetaGerSearch::class, 'tips']);
    Route::get('/plugins/opensearch.xml', [StartpageController::class, 'loadPlugin'])->name("opensearch");
    Route::get('owi', function () {
        return redirect('https://metager.de/klassik/en/owi/');
    });
    Route::get('MG20', function () {
        return redirect('https://metager.de/klassik/MG20');
    });
    Route::get('databund', function () {
        return redirect('https://metager.de/klassik/databund');
    });
    Route::get("lang", [LangSelector::class, "index"])->name("lang-selector");

    Route::group(['prefix' => 'app'], function () {
        Route::get(
            '/',
            function () {
                return view('app')
                    ->with('title', trans('titles.app'))
                    ->with('navbarFocus', 'dienste');
            }
        );
        Route::get(
            'metager',
            function () {
                return response()->streamDownload(
                    function () {
                        $fh = null;
                        try {
                            $fh = fopen("https://gitlab.metager.de/open-source/app-en/-/raw/latest/app/release_manual/app-release_manual.apk", "r");
                            while (!feof($fh)) {
                                echo (fread($fh, 1024));
                            }
                        } catch (\Exception $e) {
                            abort(404);
                        } finally {
                            if ($fh != null) {
                                fclose($fh);
                            }
                        }
                    }
                    ,
                    'MetaGerSearch.apk',
                    ["Content-Type" => "application/vnd.android.package-archive"]
                );
            }
        );
        Route::get(
            'maps',
            function () {
                return response()->streamDownload(
                    function () {
                        $fh = null;
                        try {
                            $fh = fopen("https://gitlab.metager.de/open-source/metager-maps-android/raw/latest/app/release/app-release.apk?inline=false", "r");
                            while (!feof($fh)) {
                                echo (fread($fh, 1024));
                            }
                        } catch (\Exception $e) {
                            abort(404);
                        } finally {
                            if ($fh != null) {
                                fclose($fh);
                            }
                        }
                    }
                    ,
                    'MetaGerMaps.apk',
                    ["Content-Type" => "application/vnd.android.package-archive"]
                );
            }
        );

        Route::get(
            'maps/version',
            function () {
                $filePath = config("metager.metager.maps.version");
                $fileContents = file_get_contents($filePath);
                return response($fileContents, 200)
                    ->header('Content-Type', 'text/plain');
            }
        );
    });

    Route::group(["prefix" => "metrics", "middleware" => "allow-local-only"], function (Router $router) {
        $router->get('/', [Prometheus::class, "metrics"]);
    });


    Route::group(['prefix' => 'health-check'], function () {
        Route::get('liveness', [HealthcheckController::class, 'liveness']);
        Route::get('liveness-scheduler', [HealthcheckController::class, 'livenessScheduler']);
    });

    Route::group(['prefix' => 'stats'], function () {
        Route::post('pl', [StatisticsController::class, 'pageLoad']);
    });
});