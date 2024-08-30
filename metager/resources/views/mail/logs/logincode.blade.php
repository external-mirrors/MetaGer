<x-mail::message>
# Login Requested

Hi,

you requested to Log into MetaGer. If that's the case please enter the code below into your login form.

## Login Code
<x-mail::panel>
    {{$logincode}}
</x-mail::panel>

If that's not the case you can simply ignore this mail.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
