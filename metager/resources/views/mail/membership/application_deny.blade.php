<x-mail::message>
# {{ $name }},

@if(empty($message))
@lang(key: 'membership/mails/application_deny.description')
@else
@lang(key: 'membership/mails/application_deny.description_reason')
<x-mail::panel>

{{ $message }}

</x-mail::panel>
@endif

@lang('membership/mails/application_deny.continue')

<x-mail::button :url="route('membership_form')" color="success">

@lang('membership/mails/application_deny.continue_button')

</x-mail::button>

\
@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>
