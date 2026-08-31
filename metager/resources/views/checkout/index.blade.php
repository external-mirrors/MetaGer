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

	Flach, nicht Anbieter dann Zahlweise: jede der elf Zahlweisen ist eine
	eigene Kachel, keine Gruppe hinter einem Anbieternamen. PayPal und
	Micropayment hatten früher je eine eigene Wahl-Seite — "Micropayment"
	oder "PayPal" stand dort zuerst, die eigentliche Zahlweise erst danach.
	Feedback dazu: wer bezahlen will, sucht eine Zahlweise, die er kennt
	("Kreditkarte", "Lastschrift"), keinen Anbieter, den er nicht kennt. Wero
	trägt aus demselben Grund seinen eigenen Namen statt "VR Payment", des
	Anbieters dahinter.

	Die Reihenfolge ist die der Datenschutzfreundlichkeit, nicht die der
	Einführung im Code: Bargeld (anonym), Wero, die drei Micropayment-
	Zahlweisen, dann die sieben PayPal-Zahlweisen. Nur Bargeld trägt eine
	Notiz — die anderen Namen sagen mit sich selbst schon, was sie sind.
	.account-tier--method (account.less) gibt jeder Kachel eine feste Höhe
	statt der inhaltsbestimmten Höhe der Paket-Kacheln: eine zweizeilige
	Beschriftung soll nicht die ganze Zeile der Kachel höher machen als die
	Nachbarzeile.

	Die sieben PayPal-Kacheln tragen die Klasse .checkout-paypal-tile und
	stehen `hidden` im Markup, wie #login-qr in login.blade.php — PayPal ist
	der einzige Anbieter, dessen Seiten ein SDK im Browser brauchen, und eine
	Kachel, die zu einer Seite führt, die ohne Javascript nichts tut, ist
	schlechter als keine Kachel. resources/js/account.js deckt alle sieben
	zusammen auf, sobald Javascript tatsächlich läuft; das SDK selbst lädt
	erst auf der Zielseite, nicht schon hier. `[hidden]` allein reicht nicht
	— .account-tier hat kein eigenes display, aber ein unerwartetes
	Elternelement könnte eins setzen, deshalb steht die Regel auch explizit
	in account.less.
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

		@if($error === 'unreachable')
			<p class="checkout-consent__error" role="alert">@lang('checkout.cash.error.unreachable')</p>
		@elseif($error === 'funding_source_not_eligible')
			<p class="checkout-consent__error" role="alert">@lang('checkout.paypal.error.not_available')</p>
		@endif

		<ul class="account-tiers">
			<li>
				<a class="account-tier account-tier--method" href="{{ route('account.checkout.cash', ['amount' => $amount]) }}">
					<img class="account-tier__icon account-tier__icon--invert" src="/img/price/letter.svg" alt="">
					<span>@lang('checkout.cash.label')</span>
					<span class="account-tier__note">@lang('checkout.page.methods.cash_note')</span>
				</a>
			</li>
			<li>
				<a class="account-tier account-tier--method" href="{{ route('account.checkout.vrpayment', ['amount' => $amount]) }}">
					<img class="account-tier__icon account-tier__icon--invert" src="/img/payment/vrpayment/wero_black.svg" alt="">
					<span>@lang('checkout.vrpayment.label')</span>
				</a>
			</li>
			<li>
				<a class="account-tier account-tier--method" href="{{ route('account.checkout.micropayment.service', ['amount' => $amount, 'service' => 'prepay']) }}">
					<img class="account-tier__icon" src="/img/price/money.svg" alt="">
					<span>@lang('checkout.micropayment.prepay.label')</span>
				</a>
			</li>
			<li>
				<a class="account-tier account-tier--method" href="{{ route('account.checkout.micropayment.service', ['amount' => $amount, 'service' => 'lastschrift']) }}">
					<img class="account-tier__icon" src="/img/funding_source/sepa.svg" alt="">
					<span>@lang('checkout.micropayment.lastschrift.label')</span>
				</a>
			</li>
			<li>
				<a class="account-tier account-tier--method" href="{{ route('account.checkout.micropayment.service', ['amount' => $amount, 'service' => 'directbanking']) }}">
					<img class="account-tier__icon" src="/img/funding_source/sofort.svg" alt="">
					<span>@lang('checkout.micropayment.directbanking.label')</span>
				</a>
			</li>
			@foreach (['paypal', 'card', 'p24', 'bancontact', 'blik', 'eps', 'mybank'] as $fundingSource)
				<li class="checkout-paypal-tile" hidden>
					<a class="account-tier account-tier--method" href="{{ route('account.checkout.paypal.service', ['amount' => $amount, 'fundingSource' => $fundingSource]) }}">
						<img class="account-tier__icon @if($fundingSource === 'card') account-tier__icon--invert @endif" src="/img/funding_source/{{ $fundingSource }}.svg" alt="">
						<span>@lang('checkout.paypal.funding.' . $fundingSource)</span>
					</a>
				</li>
			@endforeach
			@if(app()->environment('local'))
				<li>
					<a class="account-tier account-tier--method" href="{{ route('account.checkout.manual', ['amount' => $amount]) }}">
						<span>@lang('checkout.manual.label')</span>
					</a>
				</li>
			@endif
		</ul>
	</section>
</div>
@endsection
