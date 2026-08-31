@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Micropayment, Schritt eins: welche der drei Unterarten. Die alte Seite
	unterschied sie über Anbieter-Logos (routes/checkout/micropayment.js,
	views/checkout/micropayment.ejs); hier stehen Namen, wie überall sonst in
	diesem Vorgang seit dem Feedback zu checkout/index.blade.php.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('checkout.micropayment.label')</h1>
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
		<ul class="account-tiers">
			<li>
				<a class="account-tier" href="{{ route('account.checkout.micropayment.service', ['amount' => $amount, 'service' => 'prepay']) }}">
					<span>@lang('checkout.micropayment.prepay.label')</span>
				</a>
			</li>
			<li>
				<a class="account-tier" href="{{ route('account.checkout.micropayment.service', ['amount' => $amount, 'service' => 'lastschrift']) }}">
					<span>@lang('checkout.micropayment.lastschrift.label')</span>
				</a>
			</li>
			<li>
				<a class="account-tier" href="{{ route('account.checkout.micropayment.service', ['amount' => $amount, 'service' => 'directbanking']) }}">
					<span>@lang('checkout.micropayment.directbanking.label')</span>
				</a>
			</li>
		</ul>
	</section>
</div>
@endsection
