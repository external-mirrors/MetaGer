@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Barzahlung — zwei Zustände auf einer Adresse.

	$reference === null: das Formular, das den Auftrag anlegt.
	$reference !== null: der angelegte Auftrag, über GET erreicht — die
	Zustimmung zur Widerrufsfrist gilt für genau einen Klick, nicht für ein
	Neuladen dieser Seite, und deshalb steht sie nicht mehr da.

	App\Http\Controllers\ChargeController::cashSubmit() schickt zwischen
	beiden Zuständen mit 303 auf sich selbst weiter (POST/redirect/GET), damit
	ein Neuladen der Ergebnis-Seite keinen zweiten Auftrag anlegt — die alte
	Kasse im Keymanager rendert nach dem Anlegen dieselbe Adresse noch einmal
	und hat genau dieses Problem.

	Bewusst ohne "Zugang sichern" — diese Seite ist eine einzelne Zahlungsart,
	kein Ort zum Verwalten. Dafür zwei Wege zurück, die auf der ursprünglichen
	Kasse im Keymanager fehlten: zur Zahlungswahl (andere Zahlungsart, gleiche
	Menge) und ganz zurück zum Konto (`cancelUrl`, ohne #charge-Anker — "ich
	will gar nicht mehr aufladen").
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.cash.label')</h1>
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

	<section class="account-section checkout-cash">
		@if($reference === null)
			<p class="account-section__lede">@lang('checkout.cash.description')</p>

			<div>
				<p>@lang('checkout.cash.note')</p>
				<ul class="checkout-cash__notes">
					<li>@lang('checkout.cash.no_large_values')</li>
					<li>@lang('checkout.cash.no_coins')</li>
					<li>@lang('checkout.cash.accepted_currencies')</li>
					<li>@lang('checkout.cash.currency_translation')</li>
					<li>@lang('checkout.cash.no_refund')</li>
				</ul>
			</div>

			@if($error === 'unreachable')
				<p class="checkout-consent__error" role="alert">@lang('checkout.cash.error.unreachable')</p>
			@elseif($error === 'consent')
				<p class="checkout-consent__error" role="alert">@lang('checkout.consent.error')</p>
			@endif

			<form method="post" action="{{ route('account.checkout.cash.submit', ['amount' => $amount]) }}">
				{{-- Enthält ein eigenes <a>, deshalb roh ausgegeben statt mit @lang. --}}
				<p class="account-section__lede">{!! __('checkout.consent.agb', ['agblink' => route('agb')]) !!}</p>

				<div class="checkout-consent">
					<input type="checkbox" name="revocation" id="checkout-revocation" required>
					<label for="checkout-revocation">{!! __('checkout.consent.label', [
						'revocation_link' => route('agb'),
						'refundlink' => route('agb') . '#rueckerstattung',
					]) !!}</label>
				</div>

				<button type="submit" class="account-btn account-btn--primary">@lang('checkout.cash.generate')</button>
			</form>
		@else
			<div class="checkout-cash__order">
				<h2 class="account-section__heading">@lang('checkout.cash.order.heading')</h2>
				<div class="checkout-cash__order-id">
					<input type="text" id="checkout-order-id" value="{{ $reference['public_id'] }}" readonly
						autocomplete="off" spellcheck="false" data-1p-ignore="true" data-lpignore="true"
						data-form-type="other" data-bwignore>
					<button class="account-save__button" type="button" data-copies="checkout-order-id"
						data-done="@lang('key-create.copy.done')" hidden>@lang('checkout.cash.order.copy')</button>
				</div>

				<h3 class="account-save__label">@lang('checkout.cash.order.address_heading')</h3>
				<p class="checkout-cash__address">{{ __('checkout.cash.order.address') }}</p>

				<p class="checkout-cash__hint">@lang('checkout.cash.order.expiration', ['date' => $reference['expiration']->isoFormat('L')])</p>
				<p class="checkout-cash__hint">@lang('checkout.cash.order.unique')</p>
			</div>
		@endif
	</section>
</div>
@endsection
