@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('membership.title')</h1>
<div class="page-description">@lang('membership.description')</div>
<form id="membership-form" method="POST">
    <div id="contact-data">
        <h3>@lang('membership.contact.title')</h3>
        <div class="input-group">
        @if(isset($errors) && $errors->has("name"))
        @foreach($errors->get("name") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <label for="name">@lang('membership.contact.name.label')</label>
        <input type="text" name="name" id="name" size="25" placeholder="@lang('membership.contact.name.placeholder')" value="{{ Request::input('name', '') }}" autofocus required />
        </div>
        <div class="input-group">
        @if(isset($errors) &&$errors->has("email"))
        @foreach($errors->get("email") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <label for="email">@lang('membership.contact.email.label')</label>
        <input type="email" name="email" id="email" placeholder="@lang('membership.contact.email.placeholder')" value="{{ Request::input('email', '') }}" />
        </div>
    </div>
    <div id="membership-fee">
        <h3>@lang('membership.fee.title')</h3>
        <div>@lang('membership.fee.description')</div>
        @if(isset($errors) && $errors->has("amount"))
        @foreach($errors->get("amount") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        @if(isset($errors) && $errors->has("custom-amount"))
        @foreach($errors->get("custom-amount") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <div class="input-group">
            <input type="radio" name="amount" id="amount-5" value="5.00" @if(!Request::has('amount') || Request::input('amount') === "5.00")checked @endif required />
            <label for="amount-5">5€</label>
        </div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-10" value="10.00" @if(Request::input('amount', '') === "10.00")checked @endif required />
            <label for="amount-10">10€</label>
        </div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-15" value="15.00" @if(Request::input('amount', '') === "15.00")checked @endif required />
            <label for="amount-15">15€</label>
        </div>
        <div class="input-group custom">
            <input type="radio" name="amount" id="amount-custom" value="custom" @if(Request::input('amount', '') === "custom")checked @endif required />
            <label for="amount-custom">@lang('membership.fee.amount.custom.label')</label>
            <input type="number" name="custom-amount" id="amount-custom-value" step="0.01" min="2.5" value="{{ Request::input('custom-amount', '5,00') }}" placeholder="@lang('membership.fee.amount.placeholder')" />
        </div>
    </div>
    <div id="membership-payment">
        <h3>@lang('membership.payment.interval.title')</h3>
        @if(isset($errors) && $errors->has("interval"))
        @foreach($errors->get("interval") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <div id="membership-interval">
            <div class="input-group annual">
                <input type="radio" name="interval" id="interval-annual" value="annual" @if(!Request::has('interval') || Request::input('interval') === "annual")checked @endif required>
                <label for="interval-annual">@lang('membership.payment.interval.annual')</label>
            </div>
            <div class="input-group six-monthly">
                <input type="radio" name="interval" id="interval-six-monthly" value="six-monthly" @if(Request::input('interval', '') === "six-monthly")checked @endif required>
                <label for="interval-six-monthly">@lang('membership.payment.interval.six-monthly')</label>
            </div>
            <div class="input-group quarterly">
                <input type="radio" name="interval" id="interval-quarterly" value="quarterly" @if(Request::input('interval', '') === "quarterly")checked @endif required>
                <label for="interval-quarterly">@lang('membership.payment.interval.quarterly')</label>
            </div>
            <div class="input-group monthly">
                <input type="radio" name="interval" id="interval-monthly" value="monthly" @if(Request::input('interval', '') === "monthly")checked @endif required>
                <label for="interval-monthly">@lang('membership.payment.interval.monthly')</label>
            </div>
        </div>
        <h3>@lang('membership.payment.method.title')</h3>
        @if(isset($errors) && $errors->has("payment-method"))
        @foreach($errors->get("payment-method") as $error)
        <div class="error">{{ $error }}</div>
        @endforeach
        @endif
        <div id="membership-payment-method">
            <input type="radio" name="payment-method" id="payment-method-directdebit" value="directdebit" @if(!Request::has('payment-method') || Request::input('payment-method') === "directdebit")checked @endif required>
            <label for="payment-method-directdebit">@lang('membership.payment.method.directdebit.label')</label>
            <input type="radio" name="payment-method" id="payment-method-banktransfer" value="banktransfer" @if(Request::input('payment-method', '') === "banktransfer")checked @endif required>
            <label for="payment-method-banktransfer">@lang('membership.payment.method.banktransfer')</label>
            <div id="directdebit-data">
            @if(isset($errors) && $errors->has("iban"))
            @foreach($errors->get("iban") as $error)
            <div class="error">{{ $error }}</div>
            @endforeach
            @endif
            <div class="input-group">
                    <label for="accountholder">@lang('membership.payment.method.directdebit.accountholder.label')</label>
                    <input type="text" name="accountholder" id="accountholder" placeholder="@lang('membership.payment.method.directdebit.accountholder.placeholder')" value="{{ Request::input('accountholder', '') }}">
                </div>
                <div class="input-group">
                    <label for="iban">IBAN</label>
                    <input type="text" name="iban" id="iban" placeholder="DE80 1234 5678 9012 3456 78" value="{{ Request::input('iban', '') }}">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-default">@lang('membership.submit')</button>
    </div>
</form>
@endsection