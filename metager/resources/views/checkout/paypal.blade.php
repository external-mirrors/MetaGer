@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	PayPal — die einzige Zahlart in diesem Vorgang, die ein SDK im Browser
	braucht. Ohne Javascript zeigt diese Seite nur die Zustimmung und einen
	Hinweis (#checkout-paypal-noscript), keine leeren SDK-Container — genau
	wie #login-qr in login.blade.php gilt hier: etwas anzubieten, das nichts
	tut, ist schlechter, als es nicht anzubieten. Deshalb bieten die sieben
	PayPal-Kacheln auf /konto/aufladen/<menge> (checkout/index.blade.php)
	diese Zahlart ohne Javascript erst gar nicht an — wer diese Seite ohne
	Javascript trotzdem erreicht (Lesezeichen, direkt eingegeben), sieht den
	Hinweis statt eines funktionslosen Formulars.

	Konfiguration (Client-ID, Client-Token, ob Kartenzahlung erlaubt ist) geht
	als data-Attribute an resources/js/checkout-paypal.js, nicht als
	versteckte Formularfelder wie in der alten EJS-Vorlage — ein frischer
	Port, keine Stelle, die deren Konvention treffen muss.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.paypal.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	<section class="checkout-summary">
		<span class="checkout-summary__amount">@lang('account.page.charge.tokens', ['amount' => \Illuminate\Support\Number::format($amount, locale: app()->getLocale())])</span>
		<a class="checkout-summary__change" href="{{ $changeAmountUrl }}">@lang('checkout.page.change')</a>
	</section>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ route('account.checkout', ['amount' => $amount]) }}">← @lang('checkout.page.methods.back')</a>
		<a class="checkout-back" href="{{ $cancelUrl }}">@lang('checkout.page.cancel')</a>
	</nav>

	<section class="account-section">
		<noscript>
			<p class="checkout-consent__error" role="alert">@lang('checkout.paypal.noscript')</p>
			<a class="account-btn account-btn--primary" href="{{ route('account.checkout', ['amount' => $amount]) }}">@lang('checkout.page.methods.back')</a>
		</noscript>

		@if($error === 'unreachable')
			<p class="checkout-consent__error" role="alert">@lang('checkout.cash.error.unreachable')</p>
		@elseif($error === 'consent')
			<p class="checkout-consent__error" role="alert">@lang('checkout.consent.error')</p>
		@endif

		<div
			id="checkout-paypal"
			hidden
			data-funding-source="{{ $fundingSource }}"
			data-client-id="{{ $clientId }}"
			data-nonce="{{ $nonce }}"
			data-direct-card-enabled="{{ $directCardEnabled ? '1' : '' }}"
			@if($clientToken) data-client-token="{{ $clientToken }}" @endif
			data-order-create-url="{{ route('account.checkout.paypal.order.create', ['amount' => $amount, 'fundingSource' => $fundingSource]) }}"
			data-order-capture-url="{{ route('account.checkout.paypal.order.capture', ['amount' => $amount, 'fundingSource' => $fundingSource]) }}"
			data-not-eligible-url="{{ route('account.checkout', ['amount' => $amount]) }}?error=funding_source_not_eligible"
			data-cancel-message="@lang('checkout.paypal.cancel')"
			data-error-message="@lang('checkout.paypal.error.generic')"
		>
			<p class="account-section__lede">{!! __('checkout.consent.agb', ['agblink' => route('agb')]) !!}</p>

			<div class="checkout-consent" id="checkout-paypal-revocation-container" hidden>
				<input type="checkbox" name="revocation" id="checkout-revocation" required>
				<label for="checkout-revocation">{!! __('checkout.consent.label', [
					'revocation_link' => route('agb'),
					'refundlink' => route('agb') . '#rueckerstattung',
				]) !!}</label>
			</div>

			<div id="checkout-paypal-message" class="checkout-consent__error" role="alert" hidden></div>

			@if($fundingSource === 'card' && $directCardEnabled)
				<template id="checkout-paypal-card-form-skeleton">
					<form id="checkout-paypal-card-form">
						<div id="checkout-paypal-card-errors" hidden>
							@foreach (['9500', '5100', '00N7', '5400', '5180', '5120', '9520', '0500', '1330', '3ds', 'generic'] as $code)
								<div id="checkout-paypal-card-error-{{ $code }}" class="checkout-consent__error" hidden>@lang('checkout.paypal.card.error.' . $code)</div>
							@endforeach
						</div>

						<div class="checkout-micropayment__field">
							<label for="checkout-paypal-card-name">@lang('checkout.paypal.card.name')</label>
							<div id="checkout-paypal-card-name"></div>
						</div>
						<div class="checkout-micropayment__field">
							<label for="checkout-paypal-card-number">@lang('checkout.paypal.card.number')</label>
							<div id="checkout-paypal-card-number"></div>
						</div>
						<div class="checkout-micropayment__field">
							<label for="checkout-paypal-card-expiration">@lang('checkout.paypal.card.expiration')</label>
							<div id="checkout-paypal-card-expiration"></div>
						</div>
						<div class="checkout-micropayment__field">
							<label for="checkout-paypal-card-cvv">@lang('checkout.paypal.card.cvv')</label>
							<div id="checkout-paypal-card-cvv"></div>
						</div>

						<button type="submit" id="checkout-paypal-card-submit" class="account-btn account-btn--primary">@lang('checkout.paypal.submit')</button>
					</form>
				</template>
				<div id="checkout-paypal-card-container"></div>
			@else
				<div id="checkout-paypal-payment-fields" hidden></div>
				<div id="checkout-paypal-payment-button" class="checkout-paypal-widget" hidden></div>
			@endif

			<p id="checkout-paypal-loading">@lang('checkout.paypal.loading')</p>
		</div>

		<p class="account-section__lede">{!! __('checkout.paypal.privacy', ['link' => $privacyUrl]) !!}</p>
	</section>
</div>
@endsection
