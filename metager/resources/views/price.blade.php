@extends('layouts.subPages', ['page' => 'preise'])

@section('title', $title)

@section('content')
{{--
	Was ein MetaGer-Schlüssel kostet.

	Lag als /keys/cost im Keymanager; der leitet dauerhaft hierher weiter. Die
	Preiszahlen kommen weiterhin von dort — App\Landing\KeyPrice fragt
	/keys/api/json/price ab —, weil der Checkout, der sie abrechnet, dort
	geblieben ist. Zwei Repositories, die je einen Preis behaupten, wäre auf
	einer Seite neben einem echten Bezahlvorgang ein Fehler, der Geld kostet.

	#payment-methods ist ein von außen verlinkter Anker: die Vorteilskarte
	„Kompromiss“ auf der Startseite zeigt darauf
	(parts/landing/benefits.blade.php) und der Keymanager tat es auch.
--}}
@php
	// Bild und Markenname sind Darstellung, kein Übersetzungsgut — deshalb
	// hier und nicht in lang/. Reihenfolge wie auf der alten Seite.
	$shortInfoIcons = [
		"/img/price/calendar-2.svg",
		"/img/price/money.svg",
		"/img/price/cogwheel.svg",
		"/img/price/metager-schloss-orange.svg",
	];

	$paymentMethods = [
		["image" => "/img/payment/vrpayment/wero_black.svg", "alt" => "Wero"],
		["image" => "/img/payment/paypal/paypal.svg", "alt" => "PayPal"],
		["image" => "/img/payment/paypal/card.svg", "alt" => "", "label" => __("price.methods.card")],
		["label" => __("price.methods.prepay")],
		["image" => "/img/payment/directbanking.png", "alt" => "Onlinebanking"],
		["image" => "/img/payment/paypal/sepa.svg", "alt" => "SEPA"],
		["image" => "/img/payment/paypal/p24.svg", "alt" => "Przelewy24"],
		["image" => "/img/payment/paypal/bancontact.svg", "alt" => "Bancontact"],
		["image" => "/img/payment/paypal/blik.svg", "alt" => "BLIK"],
		["image" => "/img/payment/paypal/eps.svg", "alt" => "EPS"],
		["image" => "/img/payment/paypal/mybank.svg", "alt" => "MyBank"],
	];
@endphp
<div id="price-page">
	<h1 class="page-title">@lang('price.headings.0')</h1>
	<p>{!! trans('price.texts.0') !!}</p>

	<ul id="price-tiers">
		@foreach($tiers as $tokens => $euro)
			<li class="price-tier">
				<span class="amount">{{ $tokens }}</span>
				<span class="price">{{ $euro }}&nbsp;€</span>
			</li>
		@endforeach
	</ul>

	<h2>@lang('price.headings.1')</h2>
	<ol id="short-info">
		{{-- $shortInfo, nicht $info: @extends reicht die Variablen der Kindansicht
		     per get_defined_vars() an das Layout weiter, und layouts/staticPages
		     rendert ein $info als Hinweisleiste. Eine Schleifenvariable dieses
		     Namens lässt die Seite mit htmlspecialchars(): array given abstürzen. --}}
		@foreach(trans('price.short-info') as $index => $shortInfo)
			<li>
				<img src="{{ $shortInfoIcons[$index] }}" alt="" aria-hidden="true">
				<h3>{{ $shortInfo['heading'] }}</h3>
				<div>{!! __("price.short-info.$index.text", ['linkapp' => $linkApp, 'linktokens' => $linkToken]) !!}</div>
			</li>
		@endforeach
	</ol>

	<h2 id="pricing">@lang('price.pricing.heading')</h2>
	<p>@lang('price.pricing.texts.0')</p>
	<p>@lang('price.pricing.texts.1')</p>

	<h2 id="payment-methods">@lang('price.payment-methods.heading')</h2>
	<p>@lang('price.payment-methods.texts.0')</p>
	<p>@lang('price.payment-methods.texts.1')</p>

	<h3>@lang('price.payment-methods.anonymous')</h3>
	<ul class="payment-methods-container">
		<li class="payment-method">
			<img src="/img/price/letter.svg" alt="" aria-hidden="true">
			<span>@lang('price.methods.cash')</span>
		</li>
	</ul>

	<h3>@lang('price.payment-methods.more')</h3>
	<ul class="payment-methods-container">
		@foreach($paymentMethods as $method)
			<li class="payment-method">
				@isset($method['image'])
					<img src="{{ $method['image'] }}" alt="{{ $method['alt'] }}"
						@if($method['alt'] === '') aria-hidden="true" @endif>
				@endisset
				@isset($method['label'])
					<span>{{ $method['label'] }}</span>
				@endisset
			</li>
		@endforeach
	</ul>

	{{--
		Der Hinweis für Geschäftskunden, an der Stelle, an der jemand ausrechnet,
		was MetaGer kostet. Wer das für eine Organisation tut, rechnet hier mit
		Token-Paketen und kommt auf eine Zahl, die nach laufender Abrechnung
		aussieht — die Firmenmitgliedschaft ist die Antwort, die er sucht, und
		sie stand bisher nirgends.

		Auf Deutsch und nur dort, wie jeder Hinweis auf die Mitgliedschaft:
		App\Support\MembershipOffer.
	--}}
	@if(\App\Support\MembershipOffer::isAdvertised())
		<div class="price-business" id="price-business">
			<h3>@lang('business.hints.price.heading')</h3>
			<p>@lang('business.hints.price.text')</p>
			<a href="{{ route('business') }}">@lang('business.hints.price.action')</a>
		</div>
	@endif

	{{--
		Wohin es von hier aus weitergeht.

		Diese Seite wird von der Seitenleiste, den Landing-Abschnitten
		(how-it-works, benefits), dem Hilfe-Index und dem Konto aus verlinkt und
		endete bis hierher im Nichts: wer sie zu Ende liest, hatte keinen Schritt
		weiter zum Schlüssel, und wer aus seinem Konto kam, keinen zurück.

		Angemeldet mit einem echten Schlüssel — nicht die Weberweiterung, die
		hier kein Konto hat und von /konto ohnehin auf die Token-Seite
		weitergeleitet würde (App\Http\Controllers\AccountController::show) —
		heißt: der Besucher hat ein Konto, also dorthin. Sonst der Einstieg in
		den Schlüsselvorgang, mit denselben Beschriftungen wie
		parts/landing/how-it-works, damit der Weg gleich benannt ist wie der,
		den der Besucher auf der Startseite schon gesehen hat.

		Der temporäre Nutzer der Weberweiterung fällt in denselben Zweig wie ein
		Besucher ohne Schlüssel: /konto ist für ihn kein Ziel, und der generische
		Einstieg ist die sichere Voreinstellung — er verwaltet seinen Zugang
		ohnehin im Fenster der Erweiterung.
	--}}
	@php($priceKeyUser = \Auth::guard('key')->user())
	<div class="price-next">
		@if($priceKeyUser !== null && !$priceKeyUser->temporary)
			<a class="account-btn account-btn--primary" href="{{ $linkAccount }}">@lang('account.page.heading')</a>
			<a class="account-btn account-btn--quiet" href="{{ $linkSearch }}">@lang('account.page.actions.search')</a>
		@else
			<a class="account-btn account-btn--primary" href="{{ $linkCreate }}">@lang('index.landing.howitworks.start')</a>
			<a class="account-btn account-btn--quiet" href="{{ $linkLogin }}">@lang('index.landing.howitworks.login')</a>
		@endif
	</div>
</div>
@endsection
