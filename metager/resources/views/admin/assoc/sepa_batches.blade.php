@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>SEPA-Sammellastschriften</h1>

        <p>
            Bereit zur Einreichung: {{ $eligibleCount }}
            @if($eligibleCount === 1) Lastschrift @else Lastschriften @endif,
            insgesamt {{ number_format($eligibleTotal, 2, ",", ".") }}&euro;.
        </p>

        @if($eligibleCount > 0)
            <form method="POST" action="{{ route('assoc_admin_sepa_batches_generate') }}">
                <input type="submit" value="Sammellastschrift erzeugen" class="btn btn-default">
            </form>
        @endif

        <h2>Bisherige Sammellastschriften</h2>
        @if($batches->isEmpty())
            <p>Noch keine Sammellastschrift erzeugt.</p>
        @else
            <table>
                <thead>
                    <th>Erzeugt</th>
                    <th>Lastschriften</th>
                    <th>Betrag</th>
                    <th>Datei</th>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                        <tr>
                            <td>{{ $batch->generated_at->format("d.m.Y H:i") }}</td>
                            <td>{{ $batch->debit_count }}</td>
                            <td>{{ number_format($batch->total_amount, 2, ",", ".") }}&euro;</td>
                            <td><a href="{{ route('assoc_admin_sepa_batch_download', ['id' => $batch->id]) }}">Herunterladen</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @include('admin.assoc._pager', ['paginator' => $batches])
        @endif
    </div>
@endsection
