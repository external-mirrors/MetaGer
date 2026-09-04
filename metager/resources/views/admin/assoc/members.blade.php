@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>Mitglieder</h1>
        <p><a href="{{ route('assoc_admin_households') }}">Haushalte ansehen</a></p>

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
                                {{ $contact->first_name . " " . $contact->last_name }}
                            </a>
                        </td>
                        <td>{{ $contact->email }}</td>
                        <td>{{ $contact->city }}</td>
                        @if($contact->membership !== null)
                            <td>
                                {{ number_format($contact->membership->amount, 2, ",", ".") }}&euro;
                                {{ $contact->membership->intervalLabel() }}
                            </td>
                            <td>{{ $contact->membership->paymentMethodLabel() }}</td>
                            <td>{{ $contact->membership->standingLabel() }}</td>
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
                        @if($company->membership !== null)
                            <td>
                                {{ number_format($company->membership->amount, 2, ",", ".") }}&euro;
                                {{ $company->membership->intervalLabel() }}
                            </td>
                            <td>{{ $company->membership->paymentMethodLabel() }}</td>
                            <td>{{ $company->membership->standingLabel() }}</td>
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
