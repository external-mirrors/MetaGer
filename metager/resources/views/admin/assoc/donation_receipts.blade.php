@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>Zuwendungsbestätigungen</h1>

        <table>
            <thead>
                <th>Erstellt</th>
                <th>Jahr</th>
                <th>Art</th>
                <th>Empfänger</th>
                <th>Betrag</th>
                <th>PDF</th>
            </thead>
            <tbody>
                @foreach($receipts as $receipt)
                    @php($payer = $receipt->payer())
                    <tr>
                        <td>{{ $receipt->generated_at?->format("d.m.Y") }}</td>
                        <td>{{ $receipt->year }}</td>
                        <td>{{ $receipt->sourceLabel() }}</td>
                        <td>{{ $payer instanceof \App\Models\Assoc\Contact ? $payer->name() : ($payer->name ?? "—") }}</td>
                        <td>{{ number_format($receipt->total_amount, 2, ",", ".") }}&euro;</td>
                        <td>
                            @if($receipt->pdf_path)
                                <a href="{{ route('assoc_admin_donation_receipt_download', ['id' => $receipt->id]) }}">Herunterladen</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('admin.assoc._pager', ['paginator' => $receipts])
    </div>
@endsection
