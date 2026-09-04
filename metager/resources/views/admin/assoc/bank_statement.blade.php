@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>Geldeingang zuordnen</h1>

        <table>
            <tr>
                <th>Datum</th>
                <td>{{ $line->booked_at->format("d.m.Y") }}</td>
            </tr>
            <tr>
                <th>Betrag</th>
                <td>{{ number_format($line->amount, 2, ",", ".") }}&euro;</td>
            </tr>
            <tr>
                <th>IBAN</th>
                <td>{{ iban_to_human_format($line->iban) }}</td>
            </tr>
            <tr>
                <th>Verwendungszweck</th>
                <td>{{ $line->reference }}</td>
            </tr>
        </table>

        <h2>Lastschrift oder Dauerauftrag suchen</h2>
        <form method="GET" action="{{ route('assoc_admin_bank_statement', ['id' => $line->id]) }}">
            <input type="text" name="q" value="{{ $search }}" placeholder="Name oder Mandatsreferenz">
            <input type="submit" value="Suchen" class="btn btn-default">
        </form>

        @if($search !== "")
            @if($candidates->isEmpty())
                <p>Keine Treffer für „{{ $search }}“.</p>
            @else
                <table>
                    <thead>
                        <th>Typ</th>
                        <th>Kontoinhaber</th>
                        <th>Betrag</th>
                        <th>Mandat</th>
                        <th>Fällig</th>
                        <th></th>
                    </thead>
                    <tbody>
                        @foreach($candidates as $candidate)
                            @php($model = $candidate["model"])
                            <tr>
                                <td>{{ $candidate["type"] === "debit" ? "Lastschrift" : "Dauerauftrag" }}</td>
                                <td>{{ $model->account_holder }}</td>
                                <td>{{ number_format($model->amount, 2, ",", ".") }}&euro;</td>
                                <td>{{ $model->mandate }}</td>
                                <td>{{ $candidate["type"] === "debit" ? $model->due_date?->format("d.m.Y") : $model->next_due_date?->format("d.m.Y") }}</td>
                                <td>
                                    <form method="POST" action="{{ route('assoc_admin_bank_statement_match', ['id' => $line->id]) }}">
                                        <input type="hidden" name="type" value="{{ $candidate["type"] }}">
                                        <input type="hidden" name="target_id" value="{{ $model->id }}">
                                        <input type="submit" value="Zuordnen" class="btn btn-success">
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif

        <p><a href="{{ route('assoc_admin_bank_statements') }}">&laquo; Zurück zur Liste</a></p>
    </div>
@endsection
