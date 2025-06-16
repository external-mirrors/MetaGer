<x-mail::message>
# {{ $name }},

@lang('membership/mails/payment_method_failed.description', ["payment_reference" => $application->payment_reference])

> `SUMA-EV`\
> `DE64 4306 0967 4075 0332 01`\
> `GENODEM1GLS`\
> `GLS Gemeinschaftsbank, Bochum`

@lang('membership/mails/payment_method_failed.edit')

<x-mail::button :url="route('membership_form', ['application_id' => $application->id])" color="success">

@lang('membership/mails/payment_method_failed.edit_button')

</x-mail::button>

@include('mail.membership.layouts.next_payments', ['payments' => $payments])

\
@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>
