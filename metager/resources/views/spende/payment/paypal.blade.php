@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('spende.headline.1')</h1>
<div id="donation">
    <script id="paypal-script" src="{{ $paypal_sdk }}" nonce="{{ $nonce }}" data-csp-nonce="{{ $nonce }}" @if(array_key_exists('client_token', $donation))data-client-token="{{ $donation['client_token'] }}"@endif></script>
    <div class="section">
        @lang('spende.headline.2', ['aboutlink' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/about')])
    </div>
    <ul id="breadcrumps">
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/') }}">{{ number_format($donation["amount"], 2, ",", ".") }}€</a></li>
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/' . $donation['amount']) }}">@lang('spende.interval.frequency.' . $donation["interval"])</a></li>
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval']) }}">@lang('spende.payment-method.methods.' . $donation["funding_source"])</a></li>
    </ul>
    <div id="content-container" class="paypal">
        @if(array_key_exists("plan_id", $donation))
        <input type="hidden" name="plan-id" value="{{ $donation['plan_id'] }}">
        <input type="hidden" name="success-url" value="{{ URL::signedRoute('thankyou', ['amount' => $donation['amount'], 'interval' => $donation['interval'], 'funding_source' => $donation['funding_source'], 'timestamp' => time()]) }}">
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
        @if($donation["interval"] !== "once" && $donation["funding_source"] === "card")
        <div id="paypal-card-recurring-hint">@lang('spende.execute-payment.card.recurring-hint')</div>
        @endif
    </div>
</div>
<form id="card-form-skeleton" class="hidden">
    <div id="card-errors" class="hidden">
        <div id="error-9500" class="error hidden">@lang('spende.execute-payment.card.error.9500')</div>
        <div id="error-5100" class="error hidden">@lang('spende.execute-payment.card.error.5100')</div>
        <div id="error-00N7" class="error hidden">@lang('spende.execute-payment.card.error.00N7')</div>
        <div id="error-5110" class="error hidden">@lang('spende.execute-payment.card.error.00N7')</div>
        <div id="error-5400" class="error hidden">@lang('spende.execute-payment.card.error.5400')</div>
        <div id="error-5180" class="error hidden">@lang('spende.execute-payment.card.error.5180')</div>
        <div id="error-5120" class="error hidden">@lang('spende.execute-payment.card.error.5120')</div>
        <div id="error-9520" class="error hidden">@lang('spende.execute-payment.card.error.9520')</div>
        <div id="error-0500" class="error hidden">@lang('spende.execute-payment.card.error.0500')</div>
        <div id="error-1330" class="error hidden">@lang('spende.execute-payment.card.error.1330')</div>
        <div id="error-generic" class="error hidden">@lang('spende.execute-payment.card.error.generic')</div>
    </div>
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
    @if(app()->environment("local"))
    <div class="input-group card-name">
        <label for="card-name">Card Rejection Trigger (Testing)</label>
        <input type="text" id="card-name">
    </div>
    @endif
    <button type="submit" id="card-submit" class="btn btn-default">@lang('spende.execute-payment.card.submit')</button>
</form>
@endsection