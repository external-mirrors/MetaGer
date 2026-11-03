<?php

namespace App\Http\Controllers;

use App\Localization;
use Carbon\Carbon;
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

    /**
     * When someone answers the phone, Monday to Friday on the office's clock.
     * Inside these hours almost always, outside them almost never.
     */
    private const OFFICE_OPENS = "09:00";
    private const OFFICE_CLOSES = "15:00";
    private const OFFICE_TIMEZONE = "Europe/Berlin";

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
            "office_hours" => [
                "from" => self::officeTime(self::OFFICE_OPENS),
                "to" => self::officeTime(self::OFFICE_CLOSES),
            ],
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

    /**
     * One office time as a <time> element, written the way the visitor's
     * locale writes times: 15:00 for de-DE and en-GB, 3:00 PM for en-US.
     */
    private static function officeTime(string $time): string
    {
        $label = Carbon::createFromFormat("H:i", $time, self::OFFICE_TIMEZONE)
            ->locale(app()->getLocale())
            ->isoFormat("LT");

        return '<time datetime="' . e($time) . '">' . e($label) . "</time>";
    }
}
