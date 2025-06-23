## @lang("membership/mails/welcome_mail.membership.next_payments"):
<x-mail::table>

| @lang("membership/mails/welcome_mail.membership.due")    | @lang("membership/mails/welcome_mail.membership.amount")       |
| :-----------: | :-----------: |
@foreach($payments as $payment)
| {{ $payment["due_date_in_the_past"] ? __("membership/mails/welcome_mail.membership.now") : $payment["due_date"]->format("d.m.Y") }} | {{ (new \NumberFormatter($locale, \NumberFormatter::CURRENCY))->formatCurrency($payment["amount"], "EUR") }} | 
@endforeach

</x-mail::table>

@if(sizeof($payments) > 2 && $payments[0]["amount"] > $payments["1"]["amount"])
@lang("membership/mails/welcome_mail.membership.next_payments_hint")
@endif