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
        <li class="done"><a href="#">{{ number_format($donation["amount"], 2, ",", ".") }}€</a></li>
        <li class="done"><a href="#">@lang('spende.interval.frequency.' . $donation["interval"])</a></li>
        <li class="done"><a href="#">@lang('spende.payment-method.methods.' . $donation["funding_source"])</a></li>
    </ul>
    <div id="content-container" class="banktransfer">
        <h2>@lang('spende.thankyou.heading')</h2>
        <div>@lang('spende.thankyou.description')</div>
		<div>@lang('spende.thankyou.taxes', ["kontakt" => LaravelLocalization::getLocalizedUrl(null, "/kontakt")])</div>
		<a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedUrl(null, '/') }}">@lang('spende.thankyou.button')</a>
    </div>
</div>
@endsection