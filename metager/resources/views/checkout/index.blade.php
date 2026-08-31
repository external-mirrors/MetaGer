@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Aufladen, Schritt zwei: die Zahlungsart. Schritt eins (die Menge) steht auf
	/konto — App\Http\Controllers\ChargeController hat die Gründe für den
	Umzug hierher.

	Bewusst ohne "Zugang sichern": diese Seite ist eine Entscheidung, keine
	Verwaltung, und die Kacheln sollen kurz und gleich groß bleiben — je mehr
	Zahlungsarten dazukommen, desto mehr fällt eine lange Beschreibung auf.
	Nur der Name der Zahlungsart steht hier; wie sie funktioniert, steht auf
	ihrer eigenen Seite.

	Die PayPal-Kachel steht `hidden` im Markup, wie #login-qr in
	login.blade.php — PayPal ist die einzige Zahlart, deren Seite ein SDK im
	Browser braucht, und eine Kachel, die zu einer Seite führt, die ohne
	Javascript nichts tut, ist schlechter als keine Kachel. resources/js/
	account.js deckt sie auf, sobald Javascript tatsächlich läuft; das SDK
	selbst lädt erst auf der Zielseite, nicht schon hier. `[hidden]` allein
	reicht nicht — .account-tier hat kein eigenes display, aber ein
	unerwartetes Elternelement könnte eins setzen, deshalb steht die Regel
	auch explizit in account.less.
--}}
<div id="checkout-page">
	<header class="account-head">
		<h1 class="page-title">@lang('account.page.charge.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	<section class="checkout-summary">
		<span class="checkout-summary__amount">@lang('account.page.charge.tokens', ['amount' => \Illuminate\Support\Number::format($amount, locale: app()->getLocale())])</span>
		<span class="checkout-summary__price">@lang('account.page.charge.price', ['price' => $price])</span>
		<a class="checkout-summary__change" href="{{ $changeAmountUrl }}">@lang('checkout.page.change')</a>
	</section>

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ $cancelUrl }}">← @lang('checkout.page.cancel')</a>
	</nav>

	<section class="account-section checkout-methods">
		<h2 class="account-section__heading">@lang('checkout.page.methods.heading')</h2>
		<ul class="account-tiers">
			<li>
				<a class="account-tier" href="{{ route('account.checkout.cash', ['amount' => $amount]) }}">
					<span>@lang('checkout.cash.label')</span>
				</a>
			</li>
			<li>
				<a class="account-tier" href="{{ route('account.checkout.micropayment', ['amount' => $amount]) }}">
					<span>@lang('checkout.micropayment.label')</span>
				</a>
			</li>
			<li>
				<a class="account-tier" href="{{ route('account.checkout.vrpayment', ['amount' => $amount]) }}">
					<span>@lang('checkout.vrpayment.label')</span>
				</a>
			</li>
			<li id="checkout-paypal-tile" hidden>
				<a class="account-tier" href="{{ route('account.checkout.paypal', ['amount' => $amount]) }}">
					<span>@lang('checkout.paypal.label')</span>
				</a>
			</li>
			@if(app()->environment('local'))
				<li>
					<a class="account-tier" href="{{ route('account.checkout.manual', ['amount' => $amount]) }}">
						<span>@lang('checkout.manual.label')</span>
					</a>
				</li>
			@endif
		</ul>
	</section>
</div>
@endsection
