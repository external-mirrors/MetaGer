@if($recurContributions->isNotEmpty())
    <h2>Daueraufträge</h2>
    <table>
        <thead>
            <th>Nächste Abbuchung</th>
            <th>Betrag</th>
            <th>Rhythmus</th>
            <th>IBAN</th>
            <th>Aktiv</th>
        </thead>
        <tbody>
            @foreach($recurContributions as $recurContribution)
                <tr>
                    <td>{{ $recurContribution->next_due_date?->format("d.m.Y") ?? "—" }}</td>
                    <td>{{ number_format($recurContribution->amount, 2, ",", ".") }}&euro;</td>
                    <td>{{ $recurContribution->frequency }}</td>
                    <td>{{ iban_to_human_format($recurContribution->iban) }}</td>
                    <td>{{ $recurContribution->active ? "Ja" : "Nein" }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
