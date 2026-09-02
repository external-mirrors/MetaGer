@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Bestellung nachschlagen — App\Http\Controllers\OrderController::lookup().

	Keine Liste, ein Formular: Zahlungs-ID eingeben, und find() leitet per
	303 auf die Detailseite weiter (POST/redirect/GET, damit ein Neuladen die
	Suche nicht wiederholt). $error ist null, "invalid" oder "not_found" —
	eine fremde Nummer ist "not_found", nichts anderes.
--}}
<div id="account-page">
	<header class="account-head">
		<h1 class="page-title">@lang('orders.lookup.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	<section class="account-section">
		<p class="account-section__lede">@lang('orders.lookup.description')</p>

		@if($error === 'invalid')
			<p class="checkout-consent__error" role="alert">@lang('orders.lookup.error.invalid')</p>
		@elseif($error === 'not_found')
			<p class="checkout-consent__error" role="alert">@lang('orders.lookup.error.not_found')</p>
		@endif

		<form method="post" action="{{ route('account.orders.find') }}" class="orders-lookup">
			<label class="orders-lookup__label" for="orders-reference">@lang('orders.lookup.placeholder')</label>
			<div class="orders-lookup__row">
				<input type="text" name="reference" id="orders-reference" inputmode="numeric"
					autocomplete="off" spellcheck="false" required
					value="{{ $reference }}"
					placeholder="@lang('orders.lookup.placeholder')">
				<button type="submit" class="account-btn account-btn--primary">@lang('orders.lookup.submit')</button>
			</div>
		</form>

	</section>

	{{--
		Ein Link und kein Knopf: .account-btn ist `display: flex` und dehnte
		sich im Raster der Karte über deren volle Breite — der Ausstieg war
		damit das größte Bedienelement der Seite, größer als „Bestellung
		anzeigen" daneben.
	--}}
	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ $accountUrl }}">@lang('checkout.page.cancel')</a>
	</nav>
</div>
@endsection
