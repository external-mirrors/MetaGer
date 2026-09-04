@if($debits->isNotEmpty())
    <h2>Lastschriften / Überweisungen</h2>
    <table>
        <thead>
            <th>Fällig</th>
            <th>Betrag</th>
            <th>IBAN</th>
            <th>Status</th>
            <th>Quelle</th>
            <th>Bescheinigung</th>
        </thead>
        <tbody>
            @foreach($debits as $debit)
                <tr>
                    <td>{{ $debit->due_date?->format("d.m.Y") }}</td>
                    <td>{{ number_format($debit->amount, 2, ",", ".") }}&euro;</td>
                    <td>{{ iban_to_human_format($debit->iban) }}</td>
                    <td>{{ $debit->status }}</td>
                    <td>{{ $debit->source }}</td>
                    <td>
                        @if($debit->donation_receipt_id !== null)
                            <a href="{{ route('assoc_admin_donation_receipt_download', ['id' => $debit->donation_receipt_id]) }}">Herunterladen</a>
                        @elseif($debit->status === "executed")
                            <form method="POST" action="{{ route('assoc_admin_debit_generate_receipt', ['debitId' => $debit->id]) }}">
                                <input type="submit" value="Erstellen" class="btn btn-default">
                            </form>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
