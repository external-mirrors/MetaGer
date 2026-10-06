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
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/') }}">{{ number_format($donation["amount"], 2, ",", ".") }}€</a></li>
        <li class="current"><a href="#">@lang('spende.breadcrumps.payment_interval')</a></li>
        <li class="next"><a href="#">@lang('spende.breadcrumps.payment_method')</a></li>
    </ul>
    <div id="content-container" class="interval">
        <h3>@lang('spende.interval.heading')</h3>
        @if($errors->has("crm"))
        <div class="error">{{ $errors->first("crm") }}</div>
        @endif
        {{-- Picking the interval hands off to suma-payments, which asks for
             the payment method itself (DonationController::checkout()). --}}
        <form method="POST" action="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/' . $donation['amount']) }}">
            <ul>
                @foreach(["once", "monthly", "quarterly", "six-monthly", "annual"] as $interval)
                <li><button type="submit" name="interval" value="{{ $interval }}">@lang('spende.interval.frequency.' . $interval)</button></li>
                @endforeach
            </ul>
        </form>
    </div>
</div>
@endsection