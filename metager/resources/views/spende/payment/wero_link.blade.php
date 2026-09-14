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
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/' . $donation['amount']) }}">@lang('spende.interval.frequency.' . $donation["interval"])</a></li>
        <li class="done"><a href="{{ LaravelLOcalization::getLocalizedUrl(null, '/spende/' . $donation['amount'] . '/' . $donation['interval']) }}">@lang('spende.payment-method.methods.' . $donation["funding_source"])</a></li>
    </ul>
    <div id="content-container" class="wero-link">
        <h3>@lang('spende.execute-payment.heading')</h3>
        <div>@lang('spende.execute-payment.wero_link.description')</div>
        @if(!empty($errors) && $errors->has("crm"))
        <div class="error">{{ $errors->first("crm") }}</div>
        @endif
        <form method="POST">
            {{-- Pre-filled from the payment-method page's shared name field
                 (carried forward as a query param), same as directdebit's own
                 form — shown, not hidden, so a donor whose browser didn't
                 carry it forward (e.g. JS disabled) can still see and fill
                 the field rather than being stuck on a required hidden input
                 with no way to satisfy it. --}}
            <div class="input-group name">
                <label for="name">@lang('spende.execute-payment.wero_link.name.label')</label>
                @if(!empty($errors) && $errors->has("name"))
                @foreach($errors->get("name") as $error)
                <div class="error">{{ $error }}</div>
                @endforeach
                @endif
                <input type="text" name="name" id="name" required placeholder="@lang('spende.execute-payment.wero_link.name.placeholder')"
                    @if(Request::filled('name'))
                    value="{{ Request::input('name') }}"
                    @endif>
            </div>
            {{-- Unlike every other method on this page, wero_link needs an
                 email — it's the only way the periodic payment link is ever
                 delivered (cutover-plan.md §4.11), so there's no anonymous
                 wero_link donation the way a one-time PayPal donation stays
                 anonymous. --}}
            <div class="input-group email">
                <label for="email">@lang('spende.execute-payment.wero_link.email.label')</label>
                @if(!empty($errors) && $errors->has("email"))
                @foreach($errors->get("email") as $error)
                <div class="error">{{ $error }}</div>
                @endforeach
                @endif
                <input type="email" name="email" id="email" required placeholder="@lang('spende.execute-payment.wero_link.email.placeholder')"
                    @if(Request::filled('email'))
                    value="{{ Request::input('email') }}"
                    @endif>
            </div>
            <button class="btn btn-default" type="submit">@lang('spende.execute-payment.wero_link.submit')</button>
        </form>
    </div>
</div>
@endsection
