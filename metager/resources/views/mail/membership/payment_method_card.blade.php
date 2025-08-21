<x-mail::message>
# {{ $name }},

@lang('membership/mails/payment_method_card.description', ["payment_reference" => $application->payment_reference])


@lang('membership/mails/payment_method_card.description2', ["payment_reference" => $application->payment_reference])

> `SUMA-EV`\
> `DE64 4306 0967 4075 0332 01`\
> `GENODEM1GLS`\
> `GLS Gemeinschaftsbank, Bochum`

@lang('membership/mails/payment_method_card.edit')

<x-mail::button :url="route('membership_form', ['application_id' => App\Models\Membership\CiviCrm::GET_EDIT_ID($application->crm_membership, now()->addWeeks(2))])" color="success">

@lang('membership/mails/payment_method_card.edit_button')

</x-mail::button>

@include('mail.membership.layouts.next_payments', ['payments' => $payments, "locale" => $application->locale])

\
@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>