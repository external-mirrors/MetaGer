@if($debits->isNotEmpty())
    <h2>Lastschriften / Überweisungen</h2>
    <table>
        <thead>
            <th>Fällig</th>
            <th>Betrag</th>
            <th>IBAN</th>
            <th>Status</th>
            <th>Quelle</th>
        </thead>
        <tbody>
            @foreach($debits as $debit)
                <tr>
                    <td>{{ $debit->due_date?->format("d.m.Y") }}</td>
                    <td>{{ number_format($debit->amount, 2, ",", ".") }}&euro;</td>
                    <td>{{ iban_to_human_format($debit->iban) }}</td>
                    <td>{{ $debit->status }}</td>
                    <td>{{ $debit->source }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
