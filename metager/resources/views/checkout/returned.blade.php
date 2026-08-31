@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Die Landeseite für eine weiterleitende Zahlungsart — micropayment zuerst,
	VR Payment und PayPal teilen sie sich, sobald sie folgen
	(App\Http\Controllers\ChargeController::returned()).

	Kein #checkout-page-Rahmen: dieser Vorgang ist hier zu Ende, nicht mitten
	drin, deshalb auch keine Paketkurzfassung und kein "andere Zahlungsart" —
	nur, was passiert ist, und der Weg zurück zum Konto.

	$paid ist kein Beleg, nur ob drüben schon eine Payment-Zeile steht — bei
	einer Weiterleitung kann das Webhook-Ereignis den Browser überholen oder
	umgekehrt.
--}}
<div id="account-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.returned.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	<section class="account-section">
		@if($paid)
			<p class="account-section__lede">@lang('checkout.returned.paid', ['amount' => \Illuminate\Support\Number::format($amount, locale: app()->getLocale())])</p>
		@else
			<p class="account-blocked">@lang('checkout.returned.pending')</p>
		@endif

		<a class="account-btn account-btn--primary" href="{{ $accountUrl }}">@lang('checkout.page.cancel')</a>
	</section>
</div>
@endsection
