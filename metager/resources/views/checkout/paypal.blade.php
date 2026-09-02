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

	**Die Überschrift nennt die gewählte Zahlweise**, nicht „Zahlung
	durchführen“. Sieben Zahlweisen führen hierher — Kreditkarte, BLIK,
	Bancontact und die anderen —, und die Seite sagte auf allen sieben
	dasselbe: die einzige Bestätigung, dass man auf der Kachel gelandet war,
	die man angeklickt hatte, war das Widget, das das SDK später zeichnet.

	**Der Ladehinweis steht außerhalb von #checkout-paypal.** Er lag darin,
	und #checkout-paypal steht `hidden`, bis das SDK geladen hat — also war
	„Zahlungsmethode wird geladen“ genau so lange unsichtbar, wie es zu
	sagen hatte. Bleibt das SDK ganz aus (Netz, Blocker), stand hier eine
	Seite mit einer Überschrift und einem Datenschutzsatz und sonst nichts.

	Konfiguration (Client-ID, Client-Token, ob Kartenzahlung erlaubt ist) geht
	als data-Attribute an resources/js/checkout-paypal.js, nicht als
	versteckte Formularfelder wie in der alten EJS-Vorlage — ein frischer
	Port, keine Stelle, die deren Konvention treffen muss.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.paypal.funding.' . $fundingSource)</h1>
		@include('partials.key-fingerprint')
	</header>

	@include('partials.checkout-steps')
	@include('partials.checkout-summary')

	<section class="account-section">
		<noscript>
			<p class="checkout-consent__error" role="alert">@lang('checkout.paypal.noscript')</p>
			<a class="account-btn account-btn--quiet checkout-submit" href="{{ route('account.checkout', ['amount' => $amount]) }}">@lang('checkout.page.methods.back')</a>
		</noscript>

		@if($error === 'unreachable')
			<p class="checkout-consent__error" role="alert">@lang('checkout.cash.error.unreachable')</p>
		@elseif($error === 'consent')
			<p class="checkout-consent__error" role="alert">@lang('checkout.consent.error')</p>
		@endif

		{{--
			`hidden` im Markup, aufgedeckt von resources/js/checkout-paypal.js,
			sobald es läuft — nicht erst, wenn das SDK antwortet. Ohne
			Javascript bleibt er verborgen, sonst stünde „wird geladen" neben
			dem <noscript>-Hinweis, dass hier nichts laden wird.
		--}}
		<p id="checkout-paypal-loading" class="checkout-notice checkout-notice--loading" hidden>@lang('checkout.paypal.loading')</p>

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
			@include('partials.checkout-consent', ['boxId' => 'checkout-paypal-revocation-container', 'boxHidden' => true])

			<div id="checkout-paypal-message" class="checkout-consent__error" role="alert" hidden></div>

			@if($fundingSource === 'card' && $directCardEnabled)
				<template id="checkout-paypal-card-form-skeleton">
					<form id="checkout-paypal-card-form" class="checkout-micropayment">
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

						<button type="submit" id="checkout-paypal-card-submit" class="account-btn account-btn--primary checkout-submit">@lang('checkout.paypal.submit')</button>
					</form>
				</template>
				<div id="checkout-paypal-card-container"></div>
			@else
				<div id="checkout-paypal-payment-fields" hidden></div>
				<div id="checkout-paypal-payment-button" class="checkout-paypal-widget" hidden></div>
			@endif
		</div>

		{{-- Enthält ein eigenes <a>, deshalb roh ausgegeben statt mit @lang. --}}
		<p class="checkout-notice">{!! __('checkout.paypal.privacy', ['link' => $privacyUrl]) !!}</p>
	</section>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ route('account.checkout', ['amount' => $amount]) }}">@lang('checkout.page.methods.back')</a>
		<a class="checkout-back" href="{{ $cancelUrl }}">@lang('checkout.page.cancel')</a>
	</nav>
</div>
@endsection
