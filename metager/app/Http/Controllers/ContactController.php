<?php

namespace App\Http\Controllers;

use App\Localization;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\View;

/**
 * /kontakt — how to reach us (email, phone, post), then where common
 * questions are answered. See resources/views/kontakt/kontakt.blade.php.
 */
class ContactController extends Controller
{
    /**
     * The number the imprint gives (impressum.info.3). It is the same in every
     * language, so it lives here rather than in twelve translation files.
     */
    private const PHONE = "+49 511 34000070";

    public function show(): View
    {
        $to_mail = Localization::getLanguage() === "de"
            ? config("metager.metager.ticketsystem.germanmail")
            : config("metager.metager.ticketsystem.englishmail");

        return view("kontakt.kontakt", [
            "title" => trans("titles.kontakt"),
            "to_mail" => $to_mail,
            "phone" => self::PHONE,
            "phone_uri" => "tel:" . str_replace(" ", "", self::PHONE),
            "navbarFocus" => "kontakt",
            "css" => [Vite::asset("resources/less/metager/pages/contact.less")],
            // kontakt.topics.<key> => where that question is answered.
            "topics" => [
                "key" => route("key-faq"),
                "orders" => route("account.orders"),
                "donation" => route("spende"),
                "membership" => route("membership_form"),
                "help" => url("hilfe"),
                "privacy" => url("datenschutz"),
            ],
        ]);
    }
}
