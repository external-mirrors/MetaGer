<x-mail::message>
# {{ $contact["addressee_display"] }},

@if(!empty($additional_message))
{{ $additional_message }}
@endif

@lang('membership/mails/welcome_mail.general', ["member_count" => $membership_count])

# @lang("membership/mails/welcome_mail.membership.title")

@lang("membership/mails/welcome_mail.membership.description", ["amount" => (new \NumberFormatter($membership->locale, \NumberFormatter::CURRENCY))->formatCurrency($payments[0]["amount"], "EUR"), "due" => $payments[0]["due_date"]->format("d.m.Y")])  

@switch($membership->payment_method)
@case("banktransfer")
@lang("membership/mails/welcome_mail.membership.banktransfer", ['interval' => __("membership/mails/welcome_mail.membership.interval." . $payments[0]["payment_interval_string"]), 'mandate' => $membership->payment_reference])

> `SUMA-EV`\
> `DE64 4306 0967 4075 0332 01`\
> `GENODEM1GLS`\
> `GLS Gemeinschaftsbank, Bochum`
@break
@case("directdebit")
@lang("membership/mails/welcome_mail.membership.directdebit", ['interval' => __("membership/mails/welcome_mail.membership.interval." . $payments[0]["payment_interval_string"]), 'mandate' => $membership->payment_reference, 'iban' => iban_to_obfuscated_format($membership->directdebit->iban)])
@break
@case("paypal")
@lang("membership/mails/welcome_mail.membership.paypal", ['interval' => __("membership/mails/welcome_mail.membership.interval." . $payments[0]["payment_interval_string"])])
@break
@case("card")
@lang("membership/mails/welcome_mail.membership.card", ['interval' => __("membership/mails/welcome_mail.membership.interval." . $payments[0]["payment_interval_string"])])
@break
@endswitch

@include('mail.membership.layouts.next_payments', ['payments' => $payments, 'locale' => $membership->locale])

@lang("membership/mails/welcome_mail.membership.cancel")


@if(\App\Localization::getLanguage() === "de")
@lang("membership/mails/welcome_mail.membership.websites")
@endif

# @lang("membership/mails/welcome_mail.key.title")

@lang("membership/mails/welcome_mail.key.description_first", ["infos" => url("keys"), "amount" => (new \NumberFormatter($membership->locale, \NumberFormatter::CURRENCY))->formatCurrency($membership->amount, "EUR")])

> {{ $membership->key }}

@if($membership->payment_method === "banktransfer")

@lang("membership/mails/welcome_mail.key.banktransfer")

@endif

@lang("membership/mails/welcome_mail.key.description_second", ["startpage_link" => url("/")])

> [{{ route("loadSettings", ["key" => $membership->key]) }}]({{ route("loadSettings", ["key" => $membership->key]) }})

@lang("membership/mails/welcome_mail.key.description_third")

## @lang("membership/mails/welcome_mail.key.extension")

> [Firefox]({{ $plugin_firefox_url }}) | [Chrome]({{ $plugin_chrome_url }}) | [Edge]({{ $plugin_edge_url }})

@lang("membership/mails/welcome_mail.key.description_fourth", ["anonymous_token_link" => url("/keys/help/anonymous-token")])\
@lang("membership/mails/welcome_mail.key.description_fifth")\
@lang("membership/mails/welcome_mail.key.description_sixth")

# @lang("membership/mails/welcome_mail.mastodon.title")

@lang("membership/mails/welcome_mail.mastodon.description_first")\
@lang("membership/mails/welcome_mail.mastodon.description_second")\
@lang("membership/mails/welcome_mail.mastodon.description_third", ["email" => $contact["email_primary.email"]])\
\
@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>
