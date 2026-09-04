@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>Geldeingänge</h1>
        <p>
            <a href="{{ route('assoc_admin_bank_statements', ['status' => 'unmatched']) }}" @if($status === 'unmatched') style="font-weight:bold" @endif>Nicht zugeordnet</a>
            &middot;
            <a href="{{ route('assoc_admin_bank_statements', ['status' => 'matched']) }}" @if($status === 'matched') style="font-weight:bold" @endif>Zugeordnet</a>
            &middot;
            <a href="{{ route('assoc_admin_bank_statements', ['status' => 'all']) }}" @if($status === 'all') style="font-weight:bold" @endif>Alle</a>
        </p>

        <form method="POST" action="{{ route('assoc_admin_bank_statements_rematch') }}">
            <input type="submit" value="Automatik erneut auf nicht zugeordnete Zeilen anwenden" class="btn btn-default">
        </form>

        <table>
            <thead>
                <th>Datum</th>
                <th>Betrag</th>
                <th>IBAN</th>
                <th>Verwendungszweck</th>
                <th>Zuordnung</th>
            </thead>
            <tbody>
                @foreach($lines as $line)
                    <tr>
                        <td>{{ $line->booked_at->format("d.m.Y") }}</td>
                        <td>{{ number_format($line->amount, 2, ",", ".") }}&euro;</td>
                        <td>{{ iban_to_human_format($line->iban) }}</td>
                        <td>{{ $line->reference }}</td>
                        <td>
                            @if($line->matched_type === null)
                                <a href="{{ route('assoc_admin_bank_statement', ['id' => $line->id]) }}">Zuordnen</a>
                            @else
                                @php($matched = $line->matched())
                                {{ $matched?->account_holder ?? "—" }}
                                ({{ $line->match_method }})
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('admin.assoc._pager', ['paginator' => $lines])
    </div>
@endsection
