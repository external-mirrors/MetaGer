<x-mail::message>
# {{ $name }},

@lang('membership/mails/application_unfinished.description')

<x-mail::button :url="$application_link" color="success">

@lang('membership/mails/application_unfinished.finish_button')

</x-mail::button>

@lang('membership/mails/application_unfinished.cancel')
\
\
@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>