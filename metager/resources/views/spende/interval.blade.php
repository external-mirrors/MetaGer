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
        <li class="done">{{ $donation["amount"] }}€</li>
        <li class="current">@lang('spende.breadcrumps.payment_interval')</li>
        <li class="next">@lang('spende.breadcrumps.payment_method')</li>
    </ul>
    <div id="content-container" class="interval">
        <h3>@lang('spende.interval.heading')</h3>
        <ul>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/once') }}">@lang('spende.interval.frequency.once')</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/monthly') }}">@lang('spende.interval.frequency.monthly')</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/quarterly') }}">@lang('spende.interval.frequency.quarterly')</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/six-monthly') }}">@lang('spende.interval.frequency.six-monthly')</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/annual') }}">@lang('spende.interval.frequency.annual')</a></li>
        </ul>
    </div>
</div>
@endsection