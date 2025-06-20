<x-mail::message>
# {{ $name }},

@if($reminder_stage === \App\Mail\Membership\PaymentReminder::REMINDER_STAGE_ABORTED)
<x-mail::panel>
@lang('membership/mails/payment_reminder.expired')
</x-mail::panel>
@lang('membership/mails/payment_reminder.description_expired')


@lang('membership/mails/payment_reminder.description_rejoin', [
    "payment_reference" => $application->payment_reference, 
    "amount" => (new \NumberFormatter($application->locale, NumberFormatter::CURRENCY))->formatCurrency($payments[0]["amount"], "EUR"),
    ])
@else
@lang('membership/mails/payment_reminder.description', [
    "payment_reference" => $application->payment_reference, 
    "date" => $payments[0]["due_date"]->isoFormat("L"), 
    "amount" => (new \NumberFormatter($application->locale, NumberFormatter::CURRENCY))->formatCurrency($payments[0]["amount"], "EUR"),
    "due" => (clone $payments[0]["due_date"])->addWeeks($due_weeks)->isoFormat("L")
    ])

@endif

> `SUMA-EV`\
> `IBAN: DE64 4306 0967 4075 0332 01`\
> `BIC: GENODEM1GLS`\
> `GLS Gemeinschaftsbank, Bochum`

@lang('membership/mails/payment_reminder.edit')

<x-mail::button :url="route('membership_form', ['application_id' => App\Models\Membership\CiviCrm::GET_EDIT_ID($application->crm_membership, $reminder_stage !== \App\Mail\Membership\PaymentReminder::REMINDER_STAGE_ABORTED ? now()->addWeeks(2) : now()->addYears(2))])" color="success">

@lang('membership/mails/payment_reminder.edit_button')

</x-mail::button>

@if($reminder_stage === \App\Mail\Membership\PaymentReminder::REMINDER_STAGE_SECOND)
<x-mail::panel>
@lang('membership/mails/payment_reminder.terminate', ["expiration" => (clone $application->end_date)->addMonth()->isoFormat("L")])
</x-mail::panel>
@endif
@if($reminder_stage === \App\Mail\Membership\PaymentReminder::REMINDER_STAGE_SECOND && $application->mastodon_id !== null)
<x-mail::panel>
@lang('membership/mails/payment_reminder.mastodon')
</x-mail::panel>
@endif
<x-mail::panel>
@lang('membership/mails/payment_reminder.key_charge', ["key" => $application->key])
</x-mail::panel>

@if($reminder_stage !== \App\Mail\Membership\PaymentReminder::REMINDER_STAGE_ABORTED)
@include('mail.membership.layouts.next_payments', ['payments' => $payments])
@endif

@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>