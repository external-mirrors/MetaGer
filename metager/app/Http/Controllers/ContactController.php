<?php

namespace App\Http\Controllers;

use App\Localization;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\View;

/**
 * /kontakt — sends people where their question is answered, then says how to
 * reach us. See resources/views/kontakt/kontakt.blade.php.
 */
class ContactController extends Controller
{
    public function show(): View
    {
        $to_mail = Localization::getLanguage() === "de"
            ? config("metager.metager.ticketsystem.germanmail")
            : config("metager.metager.ticketsystem.englishmail");

        return view("kontakt.kontakt", [
            "title" => trans("titles.kontakt"),
            "to_mail" => $to_mail,
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
