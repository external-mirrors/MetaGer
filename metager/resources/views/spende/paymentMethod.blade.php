@extends('layouts.subPages')

@section('title', $title)

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
        <li class="current"><a href="#">@lang('spende.breadcrumps.payment_method')</a></li>
    </ul>
    <div id="content-container" class="paymentMethod">
        <h3>@lang("spende.payment-method.heading")</h3>
        @if($donation['interval'] !== 'once')
        {{-- Collected once here, reused on whichever method the donor picks below,
             instead of asking again on the directdebit/paypal steps. Not asked for
             a one-time banktransfer or one-time PayPal donation — those stay
             anonymous. --}}
        <div class="input-group name">
            <label for="donor-name">@lang('spende.payment-method.name.label')</label>
            <input type="text" id="donor-name" required placeholder="@lang('spende.payment-method.name.placeholder')">
        </div>
        @endif
        <ul id="payment-methods">
            <li>
                <a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/banktransfer') }}">
                    <div class="text">@lang('spende.payment-method.methods.banktransfer')</div>
                </a>
            </li>
            <li>
                <a data-carries-name href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/directdebit') }}">
                    <div class="image"><img src="/img/funding_source/sepa.svg" alt="SEPA"></div>
                </a>
            </li>
            <li class="paypal">
                {{-- The single PayPal tile — wallet and every alternative payment
                     method (giropay, sofort, ideal, ...) are now offered on
                     suma-payments' own hosted checkout page, not chosen here. --}}
                <a data-carries-name href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/paypal/paypal') }}">
                    <div class="image"><img src="/img/funding_source/paypal.svg" alt="PayPal"></div>
                </a>
            </li>
            @if($donation["amount"] >= 5)
            <li class="paypal">
                <a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/paypal/card') }}">
                    <div class="image"><img class="invert-dark" src="/img/funding_source/card.svg" alt="Credit-/Debitcard"></div>
                    <div class="text">@lang('spende.payment-method.methods.card')</div>
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>
@endsection