@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>
            @if($type === "contact")
                {{ $payer->first_name }} {{ $payer->last_name }}
            @else
                {{ $payer->name }}
            @endif
        </h1>

        @if($type === "contact")
            <p>E-Mail: {{ $payer->email }}</p>
        @endif
        <p>
            Adresse: {{ $payer->street }}, {{ $payer->postal_code }} {{ $payer->city }}
            @if($payer->country){{ ", " . $payer->country }}@endif
        </p>
        @if($payer->civicrm_id !== null)
            <p>CiviCRM-ID: {{ $payer->civicrm_id }}</p>
        @endif

        @if($payer->membership !== null)
            @php($membership = $payer->membership)
            <h2>Mitgliedschaft</h2>
            <table>
                <tr>
                    <th>Typ</th>
                    <td>{{ $membership->membership_type }}{{ $membership->reduced ? " (ermäßigt)" : "" }}</td>
                </tr>
                <tr>
                    <th>Beitrag</th>
                    <td>
                        {{ number_format($membership->amount, 2, ",", ".") }}&euro;
                        {{ $membership->intervalLabel() }}
                    </td>
                </tr>
                <tr>
                    <th>Zahlungsmethode</th>
                    <td>
                        {{ $membership->paymentMethodLabel() }}
                        @if($membership->payment_reference)
                            ({{ $membership->payment_reference }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ $membership->standingLabel() }}</td>
                </tr>
                <tr>
                    <th>Beitritt</th>
                    <td>{{ $membership->join_date?->format("d.m.Y") }}</td>
                </tr>
                <tr>
                    <th>Start</th>
                    <td>{{ $membership->start_date?->format("d.m.Y") }}</td>
                </tr>
                <tr>
                    <th>Ende</th>
                    <td>{{ $membership->end_date?->format("d.m.Y") ?? "—" }}</td>
                </tr>
                @if($membership->reduced_until !== null)
                    <tr>
                        <th>Ermäßigung bis</th>
                        <td>{{ $membership->reduced_until->format("d.m.Y") }}</td>
                    </tr>
                @endif
                @if($membership->key_id !== null)
                    <tr>
                        <th>MetaGer Key</th>
                        <td>{{ $membership->key_id }}</td>
                    </tr>
                @endif
                @if($membership->mastodon_id !== null)
                    <tr>
                        <th>Mastodon</th>
                        <td>{{ $membership->mastodon_id }}</td>
                    </tr>
                @endif
            </table>
        @else
            <p>Keine Mitgliedschaft.</p>
        @endif

        @include('admin.assoc._debits', ['debits' => $payer->debits])
        @include('admin.assoc._recur_contributions', ['recurContributions' => $payer->recurContributions])
        @include('admin.assoc._donation_receipt_settings', ['payer' => $payer, 'payerType' => $type])

        <p><a href="{{ route('assoc_admin_members') }}">&laquo; Zurück zur Mitgliederliste</a></p>
    </div>
@endsection
