@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	PayPal, Schritt eins: welche der sieben Zahlweisen. Dieselbe Struktur wie
	checkout/micropayment-index.blade.php — nur der Name der Zahlweise steht
	auf der Kachel, wie es die Kacheln überall sonst in diesem Vorgang tun.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.paypal.label')</h1>
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

	<section class="account-section checkout-methods">
		<h2 class="account-section__heading">@lang('checkout.page.methods.heading')</h2>

		@if($error === 'funding_source_not_eligible')
			<p class="checkout-consent__error" role="alert">@lang('checkout.paypal.error.not_available')</p>
		@endif

		<ul class="account-tiers">
			@foreach (['paypal', 'card', 'p24', 'bancontact', 'blik', 'eps', 'mybank'] as $fundingSource)
				<li>
					<a class="account-tier" href="{{ route('account.checkout.paypal.service', ['amount' => $amount, 'fundingSource' => $fundingSource]) }}">
						<span>@lang('checkout.paypal.funding.' . $fundingSource)</span>
					</a>
				</li>
			@endforeach
		</ul>
	</section>
</div>
@endsection
