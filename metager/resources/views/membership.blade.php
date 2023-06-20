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
        <label for="name">@lang('membership.contact.name.label')</label>
        <input type="text" name="name" id="name" size="25" placeholder="@lang('membership.contact.name.placeholder')" autofocus/>
        </div>
        <div class="input-group">
        <label for="email">@lang('membership.contact.email.label')</label>
        <input type="email" name="email" id="email" placeholder="@lang('membership.contact.email.placeholder')" required />
        </div>
    </div>
    <div id="membership-fee">
        <h3>@lang('membership.fee.title')</h3>
        <div>@lang('membership.fee.description')</div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-5" value="5.00" checked required />
            <label for="amount-5">5€</label>
        </div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-10" value="10.00" required />
            <label for="amount-10">10€</label>
        </div>
        <div class="input-group">
            <input type="radio" name="amount" id="amount-15" value="15.00" required />
            <label for="amount-15">15€</label>
        </div>
        <div class="input-group custom">
            <input type="radio" name="amount" id="amount-custom" value="custom" required />
            <label for="amount-custom">@lang('membership.fee.amount.custom.label')</label>
            <input type="number" name="custom-amount" id="amount-custom-value" step="0.01" min="5" value="5,00" placeholder="@lang('membership.fee.amount.placeholder')" />
        </div>
    </div>
    <div id="membership-payment">
        <h3>@lang('membership.payment.interval.title')</h3>
        <div id="membership-interval">
            <div class="input-group annual">
                <input type="radio" name="interval" id="interval-annual" value="annual" checked required>
                <label for="interval-annual">@lang('membership.payment.interval.annual')</label>
            </div>
            <div class="input-group six-monthly">
                <input type="radio" name="interval" id="interval-six-monthly" value="six-monthly" required>
                <label for="interval-six-monthly">@lang('membership.payment.interval.six-monthly')</label>
            </div>
            <div class="input-group quarterly">
                <input type="radio" name="interval" id="interval-quarterly" value="quarterly" required>
                <label for="interval-quarterly">@lang('membership.payment.interval.quarterly')</label>
            </div>
            <div class="input-group monthly">
                <input type="radio" name="interval" id="interval-monthly" value="monthly" required>
                <label for="interval-monthly">@lang('membership.payment.interval.monthly')</label>
            </div>
        </div>
        <h3>@lang('membership.payment.method.title')</h3>
        <div id="membership-payment-method">
            <input type="radio" name="payment-method" id="payment-method-directdebit" value="directdebit" checked required>
            <label for="payment-method-directdebit">@lang('membership.payment.method.directdebit.label')</label>
            <input type="radio" name="payment-method" id="payment-method-banktransfer" value="banktransfer" required>
            <label for="payment-method-banktransfer">@lang('membership.payment.method.banktransfer')</label>
            <div id="directdebit-data">
            <div class="input-group">
                    <label for="accountholder">@lang('membership.payment.method.directdebit.accountholder.label')</label>
                    <input type="text" name="accountholder" id="accountholder" placeholder="@lang('membership.payment.method.directdebit.accountholder.placeholder')">
                </div>
                <div class="input-group">
                    <label for="iban">IBAN</label>
                    <input type="text" name="iban" id="iban" placeholder="DE80 1234 5678 9012 3456 78">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-default">@lang('membership.submit')</button>
    </div>
</form>
@endsection