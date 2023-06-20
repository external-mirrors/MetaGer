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
    <div id="content-container" class="directdebit">
        <h3>@lang('spende.execute-payment.heading')</h3>
        <div>@lang('spende.execute-payment.directdebit.description')</div>
        <form method="POST">
            <div class="input-group name">
                <label for="name">@lang('spende.execute-payment.directdebit.name.label')</label>
                @if(!empty($errors) && $errors->has("name"))
                @foreach($errors->get("name") as $error)
                <div class="error">{{ $error }}</div>
                @endforeach
                @endif
                <input type="text" name="name" id="name" required placeholder="@lang('spende.execute-payment.directdebit.name.placeholder')"
                    @if(Request::filled('name'))
                    value="{{ Request::input('name') }}"
                    @endif>
            </div>
            <div class="input-group iban">
                <label for="iban">@lang('spende.execute-payment.directdebit.iban.label')</label>
                @if(!empty($errors) && $errors->has("iban"))
                @foreach($errors->get("iban") as $ibanError)
                <div class="error">{{ $ibanError }}</div>
                @endforeach
                @endif
                <input type="text" name="iban" id="iban" required placeholder="@lang('spende.execute-payment.directdebit.iban.placeholder')"
                @if(Request::filled('iban'))
                value="{{ Request::input('iban') }}"
                @endif>
            </div>
            <button class="btn btn-default" type="submit">@lang('spende.execute-payment.directdebit.submit')</button>
        </form>
    </div>
</div>
@endsection