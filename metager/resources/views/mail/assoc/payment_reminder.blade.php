<x-mail::message>
# {{ $name }},

@if($stage === \App\Mail\Assoc\PaymentReminder::STAGE_TERMINATED)
<x-mail::panel>
@lang('membership/mails/payment_reminder.expired')
</x-mail::panel>
@lang('membership/mails/payment_reminder.description_expired')

@lang('membership/mails/payment_reminder.description_rejoin', [
    "payment_reference" => $paymentReference,
    "amount" => (new \NumberFormatter($recipientLocale, \NumberFormatter::CURRENCY))->formatCurrency($amount, "EUR"),
])
@else
@lang('membership/mails/payment_reminder.description', [
    "payment_reference" => $paymentReference,
    "date" => $dueDate->isoFormat("L"),
    "amount" => (new \NumberFormatter($recipientLocale, \NumberFormatter::CURRENCY))->formatCurrency($amount, "EUR"),
    "due" => (clone $dueDate)->addWeeks($dueWeeks)->isoFormat("L"),
])
@endif

> `SUMA-EV`\
> `IBAN: DE64 4306 0967 4075 0332 01`\
> `BIC: GENODEM1GLS`\
> `GLS Gemeinschaftsbank, Bochum`

@lang('membership/mails/payment_reminder.edit')

<x-mail::button :url="route('membership_form')" color="success">
@lang('membership/mails/payment_reminder.edit_button')
</x-mail::button>

@if($stage === \App\Mail\Assoc\PaymentReminder::STAGE_SECOND)
<x-mail::panel>
@lang('membership/mails/payment_reminder.terminate', ["expiration" => $terminationDate->isoFormat("L")])
</x-mail::panel>
@endif

@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>
