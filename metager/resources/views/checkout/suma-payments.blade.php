@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Bezahlt wird bei suma-payments (App\Authentication\SumaPaymentsChargeIssuer):
	erst die Zustimmung zum vorzeitigen Erlöschen des Widerrufsrechts, dann
	legt der Keyserver den Auftrag an und die Auswahl dort übernimmt.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.suma.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	@include('partials.checkout-steps')
	@include('partials.checkout-summary')

	<section class="account-section">
		@if($error === 'unreachable')
			<p class="checkout-consent__error" role="alert">@lang('checkout.suma.error.unreachable')</p>
		@elseif($error === 'consent')
			<p class="checkout-consent__error" role="alert">@lang('checkout.consent.error')</p>
		@endif

		<p class="account-section__lede">@lang('checkout.suma.lede', ['methods' => implode(', ', array_map(fn ($m) => __('checkout.suma.methods.' . $m), \App\Authentication\SumaPaymentsChargeIssuer::METHODS))])</p>

		<form method="post" action="{{ route('account.checkout.suma.submit', ['amount' => $amount]) }}" class="checkout-form">
			@include('partials.checkout-consent')

			<p class="checkout-notice">@lang('checkout.suma.privacy')</p>

			<button type="submit" class="account-btn account-btn--primary checkout-submit">@lang('checkout.suma.submit')</button>
		</form>
	</section>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ $cancelUrl }}">@lang('checkout.page.cancel')</a>
	</nav>
</div>
@endsection
