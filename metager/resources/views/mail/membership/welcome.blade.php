<x-mail::message>
# {{ $contact["addressee_display"] }},

@lang('membership/welcome_mail.general', ["member_count" => $membership_count])

# @lang("membership/welcome_mail.membership.title")

@lang("membership/welcome_mail.membership.description", ["amount" => number_format($payments[0]["amount"], 2, ","), "due" => $payments[0]["due_date"]->format("d.m.Y")])  

@switch($membership["Beitrag.Zahlungsweise:label"])
@case("Banküberweisung")
@lang("membership/welcome_mail.membership.banktransfer", ['interval' => __("membership/welcome_mail.membership.interval." . $payments[0]["payment_interval_string"]), 'mandate' => $membership["Beitrag.Zahlungsreferenz"]])

> `SUMA-EV`\
> `DE64 4306 0967 4075 0332 01`\
> `GENODEM1GLS`\
> `GLS Gemeinschaftsbank, Bochum`
@break
@case("Lastschrift")
@lang("membership/welcome_mail.membership.directdebit", ['interval' => __("membership/welcome_mail.membership.interval." . $payments[0]["payment_interval_string"]), 'mandate' => $membership["Beitrag.Zahlungsreferenz"], 'iban' => iban_to_obfuscated_format($membership["Beitrag.IBAN"])])
@break
@endswitch

## @lang("membership/welcome_mail.membership.next_payments"):
<x-mail::table>

| @lang("membership/welcome_mail.membership.due")    | @lang("membership/welcome_mail.membership.amount")       |
| :-----------: | :-----------: |
@foreach($payments as $payment)
| {{ $payment["due_date_in_the_past"] ? __("membership/welcome_mail.membership.now") : $payment["due_date"]->format("d.m.Y") }} | {{ number_format($payment["amount"], 2, ",") }}€ | 
@endforeach

</x-mail::table>

@if(\App\Localization::getLanguage() === "de")
# @lang("membership/welcome_mail.websites.title")

@lang("membership/welcome_mail.websites.description")
@endif

# @lang("membership/welcome_mail.key.title")

@lang("membership/welcome_mail.key.description_first", ["infos" => url("keys")])

> {{ $membership["MetaGer_Key.Key"] }}

@lang("membership/welcome_mail.key.description_second", ["startpage_link" => url("/")])

> [{{ route("loadSettings", ["key" => $membership["MetaGer_Key.Key"]]) }}]({{ route("loadSettings", ["key" => $membership["MetaGer_Key.Key"]]) }})

@lang("membership/welcome_mail.key.description_third")

## @lang("membership/welcome_mail.key.extension")

> [Firefox]({{ $plugin_firefox_url }}) | [Chrome]({{ $plugin_chrome_url }}) | [Edge]({{ $plugin_edge_url }})

@lang("membership/welcome_mail.key.description_fourth", ["anonymous_token_link" => url("/keys/help/anonymous-token")])\
@lang("membership/welcome_mail.key.description_fifth")\
@lang("membership/welcome_mail.key.description_sixth")

# @lang("membership/welcome_mail.mastodon.title")

@lang("membership/welcome_mail.mastodon.description_first")\
@lang("membership/welcome_mail.mastodon.description_second")\
@lang("membership/welcome_mail.mastodon.description_third", ["email" => $contact["email_primary.email"]])\
\
@lang("membership/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>
