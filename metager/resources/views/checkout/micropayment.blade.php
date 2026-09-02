@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Micropayment, Schritt drei: die Zustimmung, und bei "prepay" die
	optionale E-Mail-Adresse. Von hier aus leitet der Server direkt zur
	Zahlungsseite bei micropayment weiter (303) — kein JS im Weg, wie beim
	Barzahlungsformular.

	Die E-Mail ist bewusst nicht required: derselbe Grund, aus dem die
	spätere Kreditkarten-Zahlart keine erzwingen soll — wer ohne Konto und
	ohne Mailadresse zahlen will, soll das können. Sie sah nur nicht danach
	aus: ein Pflichtfeld und ein freiwilliges Feld waren hier nicht zu
	unterscheiden, deshalb trägt die Beschriftung jetzt „(optional)“, wie die
	freiwilligen Felder des Rechnungsformulars auch.

	**„Sie werden weitergeleitet“ steht über dem Knopf, nicht darunter.** Was
	beim Klick passiert — die Seite verlassen, bei micropayment bezahlen,
	zurückkommen — ist die wichtigste Auskunft dieser Seite und stand als
	letzter Absatz unter dem Knopf, also hinter der Entscheidung.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.micropayment.' . $service . '.label')</h1>
		@include('partials.key-fingerprint')
	</header>

	@include('partials.checkout-steps')
	@include('partials.checkout-summary')

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
					<input type="email" name="email" id="checkout-micropayment-email" autocomplete="email"
						aria-describedby="checkout-micropayment-email-hint">
					<p class="checkout-micropayment__hint" id="checkout-micropayment-email-hint">@lang('checkout.micropayment.prepay.email.description')</p>
				</div>
			@endif

			@include('partials.checkout-consent')

			{{-- Enthält eigene <a>, deshalb roh ausgegeben statt mit @lang. --}}
			<p class="checkout-notice">{!! __('checkout.micropayment.privacy', [
				'link' => $privacyUrl,
				'link_text' => 'Micropayment',
			]) !!}</p>

			<button type="submit" class="account-btn account-btn--primary checkout-submit">@lang('checkout.micropayment.submit')</button>
		</form>
	</section>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ route('account.checkout', ['amount' => $amount]) }}">@lang('checkout.page.methods.back')</a>
		<a class="checkout-back" href="{{ $cancelUrl }}">@lang('checkout.page.cancel')</a>
	</nav>
</div>
@endsection
