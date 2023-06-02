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
        <li class="done">@lang('spende.payment-method.methods.' . $donation["funding_source"])</li>
    </ul>
    <div id="content-container" class="paypal-subscription">
        @if(array_key_exists("plan_id", $donation))
        <input type="hidden" name="plan-id" value="{{ $donation['plan_id'] }}">
        @else
        <input type="hidden" name="order-url" value="{{ LaravelLocalization::getLocalizedUrl(null, null) . '/order' }}">
        @endif
        <input type="hidden" name="amount" value="{{ $donation['amount'] }}">
        <input type="hidden" name="interval" value="{{ $donation['interval'] }}">
        <input type="hidden" name="funding_source" value="{{ $donation['funding_source'] }}">
        <h3>@lang('spende.execute-payment.heading')</h3>
       <div id="paypal-buttons"></div>
    </div>
</div>
@endsection