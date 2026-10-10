<?php

namespace App\Http\Controllers;

use App\Landing\AppCallback;
use App\Localization\LocaleContext;
use Illuminate\Http\Request;

/**
 * "Become a member" — a redirect to suma-crm's application form, and nothing
 * else.
 *
 * suma-crm owns the application, its review, the welcome mail and the member
 * portal; suma-payments owns reminders and collecting the fee. This app used
 * to do all of that itself against CiviCRM, and every link on the site (the
 * sidebar, the landing page, the donation page's upsell,
 * AccountController's membershipUrl) still points here, so this is where they
 * are handed over.
 */
class MembershipController extends Controller
{
    /**
     * `$application_id` is accepted and ignored: resume links in mails the
     * legacy form sent out still carry one, and they should land where a new
     * application starts rather than on a 404.
     *
     * `?lang=` carries this request's already-resolved locale
     * (App\Localization\LocaleContext, bound by ResolveLocale before this
     * ever runs) across to suma-crm's own App\Localization\CrmLocale::resolve(),
     * which understands the very same `?lang=` parameter and priority order
     * by design — see that class's own docblock in the suma-crm repository.
     * Without it, suma-crm would have to resolve the locale fresh from
     * Accept-Language alone, which is not necessarily the same answer this
     * app's own richer resolution (URL prefix, `mg_locale` cookie included)
     * already gave for this exact request.
     *
     * `key` rides along too: an already-recognised MetaGer user (cookie,
     * header, or query) clicking "become a member" should land on suma-crm's
     * form already tied to their own key.
     *
     * The MetaGer app's `keystore`/`variant` markers ride along as well:
     * suma-crm carries them through its form and checkout and hands the key
     * back to the app from its thanks page (App\Keys\AppCallback there,
     * {@see AppCallback} here). Dropped, the key an app user gets with their
     * membership never reaches the app.
     */
    public function form(Request $request, ?string $application_id = null)
    {
        $params = ["lang" => app(LocaleContext::class)->locale];

        $key = $this->keyOfVisitor($request);
        if ($key !== null) {
            $params["key"] = $key;
        }

        $params += AppCallback::markers($request);

        return redirect()->away(config("metager.metager.crm.url") . "/mitglied-werden?" . http_build_query($params));
    }

    /**
     * Dieselbe Reihenfolge und dieselbe `trim()`-Regel wie in
     * {@see \App\Authentication\KeyAuthGuard::user()} und
     * {@see LoginController::carriesKey()} — nur gibt diese Frage den Wert
     * zurück, weil er an suma-crm weitergereicht wird.
     *
     * Nicht auf UUID-Form geprüft: der Keyserver faltet alte
     * Nicht-UUID-Schlüssel per MD5 in denselben Raum
     * ({@see \App\Authentication\KeyIssuer}), und wer noch einen davon hat,
     * soll ihn behalten.
     */
    private function keyOfVisitor(Request $request): ?string
    {
        $candidates = [
            $request->input("key"),
            $request->header("key"),
            $request->cookie("key"),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && trim($candidate) !== "") {
                return trim($candidate);
            }
        }

        return null;
    }
}
