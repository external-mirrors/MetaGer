{{-- Shared by _ledger.blade.php (a membership's ongoing balance) and
     debit.blade.php (one Debit's own history) — same table/form, only the
     entries and the form's target action differ. --}}
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
                    {{-- The real transaction date where one exists, not
                         created_at (which is just whenever the importer/cron
                         happened to run). --}}
                    <td>{{ ($entry->bankStatementLine?->booked_at ?? $entry->created_at)->format("d.m.Y") }}</td>
                    <td>{{ $entry->kindLabel() }}</td>
                    <td>{{ number_format($entry->amount, 2, ",", ".") }}&euro;</td>
                    <td>{{ $entry->channelLabel() ?? "—" }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<form method="POST" action="{{ $formAction }}">
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
