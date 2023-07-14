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
        <li class="current"><a href="#">@lang('spende.breadcrumps.amount')</a></li>
        <li class="next"><a href="#">@lang('spende.breadcrumps.payment_interval')</a></li>
        <li class="next"><a href="#">@lang('spende.breadcrumps.payment_method')</a></li>
    </ul>
    <div id="content-container" class="amount">
        <h3>@lang('spende.headline.3')</h3>
        <div>@lang('spende.amount.description')</div>
        <ul>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5.00#breadcrumps') }}">5€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/10.00#breadcrumps') }}">10€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/15.00#breadcrumps') }}">15€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/20.00#breadcrumps') }}">20€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/25.00#breadcrumps') }}">25€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/50.00#breadcrumps') }}">50€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/100.00#breadcrumps') }}">100€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/200.00#breadcrumps') }}">200€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/300.00#breadcrumps') }}">300€</a></li>
            <input type="checkbox" name="custom-amount-switch" id="custom-amount-switch">
            <li class="grow-x custom-amount">
                <form action="">
                    <input type="number" name="amount" id="amount" step="0.01" min="0" placeholder="5.50" required>
                    <button class="btn-default btn" type="submit">OK</button>
                </form>
            </li>
            <li class="grow-x custom-amount-switch"><label for="custom-amount-switch">@lang('spende.amount.custom')</label></li>
        </ul>
        <div>@lang('spende.amount.taxes')</div>
        
        <div id="other">
            <div id="bank-transfer">
                <h3>@lang('spende.amount.banktransfer.title')</h3>
                <div class="bankaccount">
                    <div class="name">SUMA-EV</div>
                    <div class="iban">DE64 4306 0967 4075 0332 01</div>
                    <div class="bic">GENODEM1GLS</div>
                    <div class="bank">GLS Gemeinschaftsbank, Bochum</div>
                </div>
                <div class="qr">
                    <img src="{{ $banktransfer_qr_uri }}" width="100%" alt="@lang('spende.amount.qr.alt')">
                    <a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/qr') }}">@lang('spende.execute-payment.banktransfer.qrdownload')</a>
                </div>
            </div>
            @if(\App\Localization::getLanguage() === "de")
            <div id="membership-hint">
                <h3>@lang('spende.amount.membershiphint.title')</h3>
                <div>@lang('spende.amount.membershiphint.description')</div>
                <a href="{{ route('membership_form') }}" class="btn btn-default">Beitrittsformular</a>
            </div>
            @endif
        </div>
        <div>@lang('spende.amount.banktransfer.description')</div>
    </div>
</div>
@endsection