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
        <li class="current">@lang('spende.breadcrumps.amount')</li>
        <li class="next">@lang('spende.breadcrumps.payment_method')</li>
        <li class="next">@lang('spende.breadcrumps.payment_execute')</li>
    </ul>
    <div id="content-container" class="amount">
        <h3>@lang('spende.headline.3')</h3>
        <ul>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">5€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">10€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">15€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">20€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">25€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">50€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">100€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">200€</a></li>
            <li><a href="{{ LaravelLocalization::getLocalizedUrl(null, '/spende/5') }}">300€</a></li>
            <input type="checkbox" name="custom-amount-switch" id="custom-amount-switch">
            <li class="grow-x custom-amount">
                <form action="">
                    <input type="number" name="amount" id="amount" step="0.01" min="0" placeholder="5.50" required>
                    <button class="btn-default btn" type="submit">OK</button>
                </form>
            </li>
            <li class="grow-x custom-amount-switch"><label for="custom-amount-switch">@lang('spende.wunschbetrag.label')</label></li>
        </ul>
    </div>
</div>
@endsection