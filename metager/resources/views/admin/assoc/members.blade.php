@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>Mitglieder</h1>

        <h2>Personen</h2>
        <table>
            <thead>
                <th>Name</th>
                <th>E-Mail</th>
                <th>Ort</th>
                <th>Beitrag</th>
                <th>Zahlungsmethode</th>
                <th>Status</th>
            </thead>
            <tbody>
                @foreach($contacts as $contact)
                    <tr>
                        <td>
                            <a href="{{ route('assoc_admin_member', ['type' => 'contact', 'id' => $contact->id]) }}">
                                {{ $contact->name() }}
                            </a>
                        </td>
                        <td>{{ $contact->email }}</td>
                        <td>{{ $contact->city }}</td>
                        @if($contact->currentMembership !== null)
                            <td>
                                {{ number_format($contact->currentMembership->amount, 2, ",", ".") }}&euro;
                                {{ $contact->currentMembership->intervalLabel() }}
                            </td>
                            <td>{{ $contact->currentMembership->paymentMethodLabel() }}</td>
                            <td>{{ $contact->currentMembership->standingLabel() }}</td>
                        @else
                            <td colspan="3">Keine Mitgliedschaft</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('admin.assoc._pager', ['paginator' => $contacts])

        <h2>Firmen</h2>
        <table>
            <thead>
                <th>Name</th>
                <th>Ort</th>
                <th>Beitrag</th>
                <th>Zahlungsmethode</th>
                <th>Status</th>
            </thead>
            <tbody>
                @foreach($companies as $company)
                    <tr>
                        <td>
                            <a href="{{ route('assoc_admin_member', ['type' => 'company', 'id' => $company->id]) }}">
                                {{ $company->name }}
                            </a>
                        </td>
                        <td>{{ $company->city }}</td>
                        @if($company->currentMembership !== null)
                            <td>
                                {{ number_format($company->currentMembership->amount, 2, ",", ".") }}&euro;
                                {{ $company->currentMembership->intervalLabel() }}
                            </td>
                            <td>{{ $company->currentMembership->paymentMethodLabel() }}</td>
                            <td>{{ $company->currentMembership->standingLabel() }}</td>
                        @else
                            <td colspan="3">Keine Mitgliedschaft</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
        @include('admin.assoc._pager', ['paginator' => $companies])
    </div>
@endsection
