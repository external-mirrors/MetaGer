@extends('layouts.subPages')

@section('title', $title)

@php
    $payerType = $debit->contact_id !== null ? "contact" : "company";
    $payerId = $debit->contact_id ?? $debit->company_id;
    $entries = $debit->ledgerEntries->sortByDesc(fn ($entry) => $entry->bankStatementLine?->booked_at ?? $entry->created_at);
@endphp

@section('content')
    <div class="card">
        <h1>
            Buchung
            @if($debit->contact !== null)
                – {{ $debit->contact->name() }}
            @elseif($debit->company !== null)
                – {{ $debit->company->name }}
            @endif
        </h1>

        <table>
            <tr><th>Fällig</th><td>{{ $debit->due_date?->format("d.m.Y") }}</td></tr>
            <tr><th>Betrag</th><td>{{ number_format($debit->amount, 2, ",", ".") }}&euro;</td></tr>
            <tr><th>Status</th><td>{{ $debit->status }}</td></tr>
            <tr><th>Quelle</th><td>{{ $debit->source }}</td></tr>
            <tr><th>Mandat</th><td>{{ $debit->mandate }}</td></tr>
            <tr><th>IBAN</th><td>{{ $debit->iban !== null ? iban_to_human_format($debit->iban) : "—" }}</td></tr>
            @if($debit->membership !== null)
                <tr>
                    <th>Mitgliedschaft</th>
                    <td>{{ $debit->membership->membership_type }}, {{ $debit->membership->intervalLabel() }}</td>
                </tr>
            @endif
        </table>

        <h2>Buchungsverlauf</h2>
        <p style="font-size: .9em;">
            Jede Belastung, Zahlung, Rücklastschrift (Erstattung + Gebühr) und Erlass, die zu dieser
            einen Buchung gehört — chronologisch, neueste zuerst.
        </p>

        @include('admin.assoc._ledger_entries', [
            'entries' => $entries,
            'formAction' => route('assoc_admin_debit_ledger_entry', ['id' => $debit->id]),
        ])

        <p><a href="{{ route('assoc_admin_member', ['type' => $payerType, 'id' => $payerId]) }}">&laquo; Zurück zum Mitglied</a></p>
    </div>
@endsection
