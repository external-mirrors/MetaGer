<x-mail::message>
Die folgenden Anträge müssen noch bearbeitet werden.

@if(sizeof($finished) > 0)
# Aufnahmeanträge ({{ sizeof($finished) }})

| Datum     | Name      | Beitrag   |   Zahlungsart |
| :-------: | :-------: | :-------: | :-----------: |
@foreach($finished as $application)
@php
$name = $application->contact !== null ? $application->contact->first_name . " " . $application->contact->last_name : $application->company->company;
$amount = match ($application->interval) {
    "monthly" => $application->amount,
    "quarterly" => $application->amount * 3,
    "six-monthly" => $application->amount * 6,
    "annual" => $application->amount * 12,
    null => 0
};
$beitrag = number_format($amount, 2, ",", ".") . "€ " . __("membership.data.payment.interval.{$application->interval}");
$payment_method = __("membership.data.payment_methods.{$application->payment_method}");
@endphp
| {{ $application->updated_at->isoFormat("LLL") }} | {{ $name }} | {{ $beitrag }} | {{ $payment_method }} |
@endforeach
@endif
<br>
@if(sizeof($updates) > 0)
# Änderungsanträge für Mitgliedschaften ({{ sizeof($updates) }})

| Datum     | Name      | Beitrag   |   Zahlungsart |
| :-------: | :-------: | :-------: | :-----------: |
@foreach($updates as $application)
@php
$name = $application->contact !== null ? $application->contact->first_name . " " . $application->contact->last_name : $application->company->company;
$amount = match ($application->interval) {
    "monthly" => $application->amount,
    "quarterly" => $application->amount * 3,
    "six-monthly" => $application->amount * 6,
    "annual" => $application->amount * 12,
    null => 0
};
$beitrag = number_format($amount, 2, ",", ".") . "€ " . __("membership.data.payment.interval.{$application->interval}");
$payment_method = __("membership.data.payment_methods.{$application->payment_method}");
@endphp
| {{ $application->updated_at->isoFormat("LLL") }} | {{ $name }} | {{ $beitrag }} | {{ $payment_method }} |
@endforeach
<br>
@endif

@if(sizeof($reductions) > 0)
# Anträge auf Beitragsminderung ({{ sizeof($reductions) }})

| Datum     | Name      | Beitrag   |   Zahlungsart |
| :-------: | :-------: | :-------: | :-----------: |
@foreach($reductions as $application)
@php
$name = $application->contact !== null ? $application->contact->first_name . " " . $application->contact->last_name : $application->company->company;
$amount = match ($application->interval) {
    "monthly" => $application->amount,
    "quarterly" => $application->amount * 3,
    "six-monthly" => $application->amount * 6,
    "annual" => $application->amount * 12,
    null => 0
};
$beitrag = number_format($amount, 2, ",", ".") . "€ " . __("membership.data.payment.interval.{$application->interval}");
$payment_method = __("membership.data.payment_methods.{$application->payment_method}");
@endphp
| {{ $application->updated_at->isoFormat("LLL") }} | {{ $name }} | {{ $beitrag }} | {{ $payment_method }} |
@endforeach
<br>
@endif

<x-mail::button :url="route('membership_admin_overview')" color="success">

Anträge bearbeiten

</x-mail::button>
</x-mail::message>
