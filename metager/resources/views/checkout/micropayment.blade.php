@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Micropayment, Schritt zwei: die Zustimmung, und bei "prepay" die
	optionale E-Mail-Adresse. Von hier aus leitet der Server direkt zur
	Zahlungsseite bei micropayment weiter (303) — kein JS im Weg, wie beim
	Barzahlungsformular.

	Die E-Mail ist bewusst nicht required: derselbe Grund, aus dem die
	spätere Kreditkarten-Zahlart keine erzwingen soll — wer ohne Konto und
	ohne Mailadresse zahlen will, soll das können.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.micropayment.' . $service . '.label')</h1>
		@include('partials.key-fingerprint')
	</header>

	<section class="checkout-summary">
		<span class="checkout-summary__amount">@lang('account.page.charge.tokens', ['amount' => \Illuminate\Support\Number::format($amount, locale: app()->getLocale())])</span>
		<a class="checkout-summary__change" href="{{ $changeAmountUrl }}">@lang('checkout.page.change')</a>
	</section>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ route('account.checkout.micropayment', ['amount' => $amount]) }}">← @lang('checkout.page.methods.back')</a>
		<a class="checkout-back" href="{{ $cancelUrl }}">@lang('checkout.page.cancel')</a>
	</nav>

	<section class="account-section">
		@if($error === 'unreachable')
			<p class="checkout-consent__error" role="alert">@lang('checkout.cash.error.unreachable')</p>
		@elseif($error === 'consent')
			<p class="checkout-consent__error" role="alert">@lang('checkout.consent.error')</p>
		@endif

		<form method="post" action="{{ route('account.checkout.micropayment.submit', ['amount' => $amount, 'service' => $service]) }}" class="checkout-micropayment">
			@if($service === 'prepay')
				<div class="checkout-micropayment__field">
					<label for="checkout-micropayment-email">@lang('checkout.micropayment.prepay.email.label')</label>
					<input type="email" name="email" id="checkout-micropayment-email" autocomplete="email">
					<p class="checkout-micropayment__hint">@lang('checkout.micropayment.prepay.email.description')</p>
				</div>
			@endif

			{{-- Enthält ein eigenes <a>, deshalb roh ausgegeben statt mit @lang. --}}
			<p class="account-section__lede">{!! __('checkout.consent.agb', ['agblink' => route('agb')]) !!}</p>

			<div class="checkout-consent">
				<input type="checkbox" name="revocation" id="checkout-revocation" required>
				<label for="checkout-revocation">{!! __('checkout.consent.label', [
					'revocation_link' => route('agb'),
					'refundlink' => route('agb') . '#rueckerstattung',
				]) !!}</label>
			</div>

			<button type="submit" class="account-btn account-btn--primary">@lang('checkout.micropayment.submit')</button>
		</form>

		<p class="account-section__lede">{!! __('checkout.micropayment.privacy', [
			'link' => $privacyUrl,
			'link_text' => 'Micropayment',
		]) !!}</p>
	</section>
</div>
@endsection
