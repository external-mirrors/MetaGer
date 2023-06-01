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
        <li class="done">{{ $donation["interval"] }}</li>
        <li class="current">@lang('spende.breadcrumps.payment_method')</li>
    </ul>
    <div id="content-container" class="paymentMethod">
        <h3>@lang("spende.payment-method.heading")</h3>
        <ul id="payment-methods"></ul>
    </div>
</div>
@endsection