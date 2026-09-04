@php
    $outstandingDonation = $payer->debits->where("source", "donation")->where("status", "executed")->whereNull("donation_receipt_id")->count();
    $outstandingMembership = $payer->debits->where("source", "membership")->where("status", "executed")->whereNull("donation_receipt_id")->count();
@endphp

<h2>Zuwendungsbestätigungen</h2>

<form method="POST" action="{{ route('assoc_admin_payer_update_preference', ['type' => $payerType, 'id' => $payer->id]) }}">
    <label>
        Einstellung für zukünftige Zahlungen:
        <select name="donation_receipt_preference">
            <option value="" @selected($payer->donation_receipt_preference === null)>Standard (aktuell: {{ $payer->effectiveDonationReceiptPreference() }})</option>
            <option value="never" @selected($payer->donation_receipt_preference === "never")>Nie</option>
            <option value="immediate" @selected($payer->donation_receipt_preference === "immediate")>Sofort, bei jeder Zahlung</option>
            <option value="annual" @selected($payer->donation_receipt_preference === "annual")>Jährlich, gesammelt</option>
        </select>
    </label>
    <input type="submit" value="Speichern" class="btn btn-default">
</form>

@if($outstandingDonation > 0 || $outstandingMembership > 0)
    <p>Offene, noch nicht bescheinigte Zahlungen jetzt zusammenfassen und bescheinigen:</p>
    @if($outstandingDonation > 0)
        <form method="POST" action="{{ route('assoc_admin_payer_generate_receipt', ['type' => $payerType, 'id' => $payer->id]) }}" style="display:inline-block; margin-right: 1em;">
            <input type="hidden" name="source" value="donation">
            <input type="submit" value="Spendenbescheinigung für {{ $outstandingDonation }} offene Spende(n) erstellen" class="btn btn-default">
        </form>
    @endif
    @if($outstandingMembership > 0)
        <form method="POST" action="{{ route('assoc_admin_payer_generate_receipt', ['type' => $payerType, 'id' => $payer->id]) }}" style="display:inline-block;">
            <input type="hidden" name="source" value="membership">
            <input type="submit" value="Beitragsbescheinigung für {{ $outstandingMembership }} offene Beiträge erstellen" class="btn btn-default">
        </form>
    @endif
@endif
