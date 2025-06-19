<x-mail::message>
Der Einzug einer Zahlung für die folgende Mitgliedschaft ist fehlgeschlagen. Die Zahlungsmethode wurde entfernt.

<x-mail::table>

| Name          | Zahlungsart       | Beitrag | Datum |
| :-----------: | :---------------: | :-----: | :---: |
| [{{ $name }}](https://suma-ev.de/wp-admin/admin.php?page=CiviCRM&q=civicrm%2Fcontact%2Fview&reset=1&cid={{ $application->crm_contact }}&selectedChild=member)   | {{ $application->payment_method }} | {{ number_format($application->amount, 2) }}€ | {{ now()->isoFormat("LLL") }} |

</x-mail::table>

# Die Antwort von Paypal war

<pre>
{{ $order }}
</pre>

</x-mail::message>
