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
                <a data-carries-name href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/paypal/card') }}">
                    <div class="image"><img class="invert-dark" src="/img/funding_source/card.svg" alt="Credit-/Debitcard"></div>
                    <div class="text">@lang('spende.payment-method.methods.card')</div>
                </a>
            </li>
            @endif
            @if($donation['interval'] !== 'once')
            {{-- wero_link (cutover-plan.md §4.11/C6) is a recurring mandate
                 only — there is no one-shot Wero payment through this
                 endpoint, so the tile itself is hidden for a one-time
                 donation rather than offered and then rejected by suma-crm. --}}
            <li>
                <a data-carries-name href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval'] . '/wero_link') }}">
                    <div class="image"><img class="invert-dark" src="/img/payment/vrpayment/wero_black.svg" alt="Wero"></div>
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>
@endsection