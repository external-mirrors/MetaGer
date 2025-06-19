<x-mail::message>
# {{ $name }},

@lang('membership/mails/reduction_reminder.description', ["date" => $application->end_date->isoFormat("L")])


@lang('membership/mails/reduction_reminder.continue')

<x-mail::button :url="route('membership_form', ['application_id' => App\Models\Membership\CiviCrm::GET_EDIT_ID($application->crm_membership, now()->addWeeks(2)), 'edit' => 'membership-fee', 'amount' => $application->amount])" color="success">

@lang('membership/mails/reduction_reminder.continue_button')

</x-mail::button>

\
@lang("membership/mails/welcome_mail.greeting"),\
[SUMA-EV](https://suma-ev.de) & [Metager]({{ url("/") }})\
Postfach 51 01 43\
D-30631 Hannover\
Tel: [+4951134000070](tel:+4934000070) Email: [verein@metager.de](mailto:verein@metager.de)
</x-mail::message>
