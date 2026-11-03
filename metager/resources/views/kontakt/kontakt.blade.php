@extends('layouts.subPages')

@section('title', $title)

@section('content')
    <div id="kontakt">
        <h1 class="page-title">{!! trans('kontakt.headline.1') !!}</h1>
        <div class="card">
            <h2>@lang('kontakt.headline.2')</h2>
            <p>@lang('kontakt.email.text', ['mail' => $to_mail])</p>
            <h5>@lang('kontakt.headline.pgp')</h5>
            <p>@lang('kontakt.email.pgp.description')</p>
            <p>@lang('kontakt.email.pgp.pubkey', ['keyserver' => 'https://keys.openpgp.org/search?q=' . urlencode($to_mail)])</p>
            <p>@lang('kontakt.email.pgp.fingerprint')</p>
        </div>
        <div class="card">
            <h2>{!! trans('kontakt.letter.1') !!}</h2>
            <p>{!! trans('kontakt.letter.2') !!}</p>
            <address>{!! trans('kontakt.letter.3') !!}</address>
        </div>
    </div>
@endsection
