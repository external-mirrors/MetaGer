@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('spende.headline.1')</h1>
<div id="donation">
    <script id="paypal-script" src="{{ $paypal_sdk }}" nonce="{{ $nonce }}" data-csp-nonce="{{ $nonce }}" @if(array_key_exists('client_token', $donation))data-client-token="{{ $donation['client_token'] }}"@endif></script>
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
        @if($donation["funding_source"] === "card" && $donation["interval"] === "once")
        <input type="hidden" name="client-token" value="{{ $donation['client_token'] }}">
        @endif
        <input type="hidden" name="amount" value="{{ $donation['amount'] }}">
        <input type="hidden" name="interval" value="{{ $donation['interval'] }}">
        <input type="hidden" name="funding_source" value="{{ $donation['funding_source'] }}">
        <h3>@lang('spende.execute-payment.heading')</h3>
       <div id="paypal-buttons"></div>
       <form id="card-form-skeleton" class="hidden">
            <div class="input-group card-number-group">
                <label for="card-number">@lang('spende.execute-payment.card.number')</label>
                <div id="card-number"></div>
            </div>
            <div class="input-group card-expiration-group">
                <label for="card-number">@lang('spende.execute-payment.card.expiration')</label>
                <div id="card-expiration"></div>
            </div>
            <div class="input-group card-cvv-group">
                <label for="card-number">@lang('spende.execute-payment.card.cvv')</label>
                <div id="card-cvv"></div>
            </div>
            <button type="submit" id="card-submit" class="btn btn-default">@lang('spende.execute-payment.card.submit')</button>
       </form>
    </div>
</div>
@endsection