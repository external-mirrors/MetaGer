@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('spende.headline.1')</h1>
<div id="donation">
    <script id="paypal-script" src="{{ $paypal_sdk }}" nonce="{{ $nonce }}" data-csp-nonce="{{ $nonce }}"></script>
    <div class="section">
        @lang('spende.headline.2', ['aboutlink' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/about'), 'beitrittlink' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/beitritt')])
    </div>
    <ul id="breadcrumps">
        <li class="done">{{ number_format($donation["amount"], 2, ",", ".") }}€</li>
        <li class="done">@lang('spende.interval.frequency.' . $donation["interval"])</li>
        <li class="current">@lang('spende.breadcrumps.payment_method')</li>
    </ul>
    <div id="content-container" class="paymentMethod">
        <h3>@lang("spende.payment-method.heading")</h3>
        <ul id="payment-methods">
            <li>
                <a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/banktransfer') }}">
                    <div class="text">@lang('spende.payment-method.methods.banktransfer')</div>
                </a>
            </li>
            <li>
                <a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/directdebit') }}">
                    <div class="image"><img src="/img/funding_source/sepa.svg" alt="SEPA"></div>
                </a>
            </li>
            <li class="paypal">
                <a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/paypal/paypal') }}">
                    <div class="image"><img src="/img/funding_source/paypal.svg" alt="PayPal"></div>
                </a>
            </li>
            <li class="paypal">
                <a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/paypal/card') }}">
                    <div class="image"><img class="invert-dark" src="/img/funding_source/card.svg" alt="Credit-/Debitcard"></div>
                    <div class="text">@lang('spende.payment-method.methods.card')</div>
                </a>
            </li>
        </ul>
    </div>
</div>
@endsection