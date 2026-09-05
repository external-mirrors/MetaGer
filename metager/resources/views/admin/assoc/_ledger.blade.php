@php
    $balance = $membership->ledgerBalance();
    $entries = $membership->ledgerEntries->sortByDesc(fn ($entry) => $entry->bankStatementLine?->booked_at ?? $entry->created_at);
@endphp

<h3>
    Kontostand: {{ number_format($balance, 2, ",", ".") }}&euro;
    @if($balance > 0) (offen)
    @elseif($balance < 0) (Guthaben)
    @endif
</h3>

@include('admin.assoc._ledger_entries', [
    'entries' => $entries,
    'formAction' => route('assoc_admin_membership_ledger_entry', ['id' => $membership->id]),
])
