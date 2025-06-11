@extends('layouts.subPages')

@section('title', $title)

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
    <h1 class="page-title">@lang('membership.title')</h1>
    <div class="success">@lang("membership.success")</div>
    <div id="data">
        <div>@lang("membership.data.description")</div>
        @if($application->contact !== null)
            <div class="input-group">
                <label for="name">@lang("membership.data.name")</label>
                <input type="text" name="name" id="name"
                    value="{{ $application->contact->title . " " . $application->contact->first_name . " " . $application->contact->last_name }}"
                    readonly>
            </div>
            <div class="input-group">
                <label for="email">@lang("membership.data.email")</label>
                <input type="text" name="email" id="email"
                    value="{{ $application->contact->email }}"
                    readonly>
            </div>
        @elseif($application->company !== null)
            <div class="input-group">
                <label for="company">@lang("membership.data.company")</label>
                <input type="text" name="company" id="company"
                    value="{{ $application->company->company }}"
                    readonly>
            </div>
            <div class="input-group">
                <label for="email">@lang("membership.data.email")</label>
                <input type="text" name="email" id="email"
                    value="{{ $application->company->email }}"
                    readonly>
            </div>
        @endif
        <div class="input-group">
            <label for="name">@lang("membership.data.amount")</label>
            @php
            $amount = match($application->interval){
                "monthly" => $application->amount,
                "quarterly" => $application->amount * 3,
                "six-monthly" => $application->amount * 6,
                "annual" => $application->amount * 12
            };
            @endphp
            <input type="text" name="amount" id="amount" value="{{ number_format($amount, 2, ",") }}€ {{ __("membership.data.payment.interval.{$application->interval}") }}" readonly>
        </div>
        <div class="input-group">
            <label for="payment_method">@lang("membership.data.payment_method")</label>
            <input type="text" name="payment_method" id="payment_method"
                value="@lang("membership.data.payment_methods.{$application->payment_method}")"
                readonly>
        </div>
    </div>
    <div id="key">
        <div class="description">
            @lang('membership.key.description')
            @switch($application->payment_method)
            @case("banktransfer")
            @case("directdebit")
            <span>@lang('membership.key.later')</span>
            @break
            @case("paypal")
            @case("card")
            <span>@lang('membership.key.now')</span>
            @break
            @endswitch
        </div>
        <div>Notieren Sie den Schlüssel bitte für Ihre Unterlagen. Sie brauchen Ihn, um sich bei Bedarf bei MetaGer anzumelden:
        <div class="copyLink">
            <input id="loadSettings" class="loadSettings" type="text" value="{{ $application->key }}" readonly>
            <button class="js-only btn btn-default">@lang('settings.copy')</button>
        </div>
         </div>
    </div>

    <a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedURL(null, '/') }}">@lang('membership.back')</a>
@endsection