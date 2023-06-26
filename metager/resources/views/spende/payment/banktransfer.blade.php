@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('spende.headline.1')</h1>
<div id="donation">
    <div class="section">
        @lang('spende.headline.2', ['aboutlink' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/about')])
    </div>
    <ul id="breadcrumps">
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/') }}">{{ number_format($donation["amount"], 2, ",", ".") }}€</a></li>
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/' . $donation['amount']) }}">@lang('spende.interval.frequency.' . $donation["interval"])</a></li>
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval']) }}">@lang('spende.payment-method.methods.' . $donation["funding_source"])</a></li>
    </ul>
    <div id="content-container" class="banktransfer">
        <h3>@lang('spende.execute-payment.heading')</h3>
        @if($donation["interval"] === "once")
        <div>@lang('spende.execute-payment.banktransfer.description.once')</div>
        @else
        <div>@lang('spende.execute-payment.banktransfer.description.recurring')</div>
        @endif
        <div id="bank-info">
            <div id="bank-account">
                <div class="name">SUMA-EV</div>
                <div class="iban">DE64 4306 0967 4075 0332 01</div>
                <div class="bic">GENODEM1GLS</div>
                <div class="bank">GLS Gemeinschaftsbank, Bochum</div>
            </div>
            <div id="bank-qr">
                <img src="{{ $donation['qr_uri'] }}" width="100%" alt="SEPA QR Code">
                <a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/banktransfer/qr') }}">@lang('spende.execute-payment.banktransfer.qrdownload')</a>
            </div>
        </div>
    </div>
</div>
@endsection