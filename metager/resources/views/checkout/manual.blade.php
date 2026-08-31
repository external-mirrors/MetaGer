@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Die Entwicklungs-Zahlungsart — nur erreichbar, wenn
	app()->environment('local') wahr ist; ChargeController::manualShow() und
	::manualSubmit() antworten sonst mit 404. Lädt sofort auf, ohne echte
	Zahlung — siehe App\Authentication\ManualChargeIssuer für das Warum.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.manual.label')</h1>
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
		<p class="account-section__lede">@lang('checkout.manual.description')</p>
		<form method="post" action="{{ route('account.checkout.manual.submit', ['amount' => $amount]) }}">
			<button type="submit" class="account-btn account-btn--primary">@lang('checkout.manual.submit')</button>
		</form>
	</section>
</div>
@endsection
