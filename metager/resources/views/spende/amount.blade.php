@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('spende.headline.1')</h1>
<div id="donation">
    <div class="section">
        @lang('spende.headline.2', ['aboutlink' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/about'), 'beitrittlink' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/beitritt')])
    </div>
    <ul id="breadcrumps">
        <li class="current">@lang('spende.breadcrumps.amount')</li>
        <li class="next">@lang('spende.breadcrumps.payment_interval')</li>
        <li class="next">@lang('spende.breadcrumps.payment_method')</li>
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
                <pre class="bankaccount">SUMA-EV
DE64 4306 0967 4075 0332 01
GENODEM1GLS
GLS Gemeinschaftsbank, Bochum</pre>
            </div>
            @if(\App\Localization::getLanguage() === "de")
            <div id="membership-hint">
                <h3>@lang('spende.amount.membershiphint.title')</h3>
                <div>@lang('spende.amount.membershiphint.description')</div>
                <a href="{{ LaravelLocalization::getLocalizedUrl(null, '/beitritt') }}" class="btn btn-default">Beitrittsformular</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection