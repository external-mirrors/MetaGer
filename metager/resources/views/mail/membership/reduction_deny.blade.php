<x-mail::message>
# {{ $name }},

@lang('membership/mails/reduction_deny.description')

<x-mail::panel>

{{ $message }}

</x-mail::panel>

@lang('membership/mails/reduction_deny.continue')

<x-mail::button :url="$update_link" color="success">

@lang('membership/mails/reduction_deny.continue_button')

</x-mail::button>

\
@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>
