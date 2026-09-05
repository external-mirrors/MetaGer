@php
    $balance = $membership->ledgerBalance();
    $entries = $membership->ledgerEntries->sortByDesc("created_at");
@endphp

<h3>
    Kontostand: {{ number_format($balance, 2, ",", ".") }}&euro;
    @if($balance > 0) (offen)
    @elseif($balance < 0) (Guthaben)
    @endif
</h3>

@if($entries->isNotEmpty())
    <table>
        <thead>
            <th>Datum</th>
            <th>Art</th>
            <th>Betrag</th>
            <th>Kanal</th>
        </thead>
        <tbody>
            @foreach($entries as $entry)
                <tr>
                    <td>{{ $entry->created_at->format("d.m.Y") }}</td>
                    <td>{{ $entry->kindLabel() }}</td>
                    <td>{{ number_format($entry->amount, 2, ",", ".") }}&euro;</td>
                    <td>{{ $entry->channelLabel() ?? "—" }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<form method="POST" action="{{ route('assoc_admin_membership_ledger_entry', ['id' => $membership->id]) }}">
    <label>
        Art:
        <select name="kind">
            <option value="waiver">Erlass (Beitragsverzicht)</option>
            <option value="refund">Erstattung</option>
        </select>
    </label>
    <label>
        Betrag: <input type="text" name="amount" placeholder="0.00">
    </label>
    <label>
        Kanal (nur bei Erstattung):
        <select name="channel">
            <option value="sepa_credit_transfer">SEPA-Überweisung</option>
            <option value="paypal">PayPal</option>
        </select>
    </label>
    <input type="submit" value="Buchen" class="btn btn-default">
</form>
