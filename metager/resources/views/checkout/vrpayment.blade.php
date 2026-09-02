@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	VR Payment / Wero — eine einzige Zahlart, deshalb keine Wahl-Seite davor
	wie bei micropayment, sondern direkt die Zustimmungsseite, wie bei
	checkout/cash.blade.php.

	`?error=vrpayment_failed` kommt von VR Payment selbst zurück (die
	failedUrl, die App\Authentication\VRPaymentChargeIssuer beim Anlegen
	mitgibt) — anders als "unreachable"/"consent" ist das keine eigene
	Fehlermeldung dieser Anwendung, sondern "die Zahlung wurde drüben
	abgelehnt".

	Wie bei micropayment steht der Weiterleitungshinweis über dem Knopf und
	nicht darunter — siehe dort.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.vrpayment.label')</h1>
		@include('partials.key-fingerprint')
	</header>

	@include('partials.checkout-steps')
	@include('partials.checkout-summary')

	<section class="account-section">
		@if($error === 'unreachable')
			<p class="checkout-consent__error" role="alert">@lang('checkout.cash.error.unreachable')</p>
		@elseif($error === 'consent')
			<p class="checkout-consent__error" role="alert">@lang('checkout.consent.error')</p>
		@elseif($error === 'vrpayment_failed')
			<p class="checkout-consent__error" role="alert">@lang('checkout.vrpayment.error.failed')</p>
		@endif

		<form method="post" action="{{ route('account.checkout.vrpayment.submit', ['amount' => $amount]) }}" class="checkout-micropayment">
			@include('partials.checkout-consent')

			{{-- Enthält eigene <a>, deshalb roh ausgegeben statt mit @lang. --}}
			<p class="checkout-notice">{!! __('checkout.vrpayment.privacy', ['link' => $privacyUrl]) !!}</p>

			<button type="submit" class="account-btn account-btn--primary checkout-submit">@lang('checkout.vrpayment.submit')</button>
		</form>
	</section>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ route('account.checkout', ['amount' => $amount]) }}">@lang('checkout.page.methods.back')</a>
		<a class="checkout-back" href="{{ $cancelUrl }}">@lang('checkout.page.cancel')</a>
	</nav>
</div>
@endsection
