@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div class="card">
        <h1>{{ $household->household_name }}</h1>
        <p>
            Adresse: {{ $household->street }}, {{ $household->postal_code }} {{ $household->city }}
            @if($household->country){{ ", " . $household->country }}@endif
        </p>
        @if($household->civicrm_id !== null)
            <p>CiviCRM-ID: {{ $household->civicrm_id }}</p>
        @endif

        @include('admin.assoc._debits', ['debits' => $household->debits])
        @include('admin.assoc._recur_contributions', ['recurContributions' => $household->recurContributions])

        <p><a href="{{ route('assoc_admin_households') }}">&laquo; Zurück zur Haushaltsliste</a></p>
    </div>
@endsection
