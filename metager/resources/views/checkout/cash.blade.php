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

	**Der Ablauf steht vor der Zustimmung, nicht dahinter.** Die Seite begann
	vorher mit einem Absatz und fünf gleich gewichteten Warnungen, und die
	Anschrift, an die der Brief überhaupt gehen soll, stand ausschließlich auf
	der Seite *nach* dem Erzeugen der Zahlungs-ID: man musste zustimmen und
	einen Auftrag anlegen, um zu erfahren, was die Aufgabe eigentlich ist.
	Jetzt stehen die drei Schritte und die Anschrift oben; die fünf Hinweise
	bleiben vollständig, aber darunter und als das, was sie sind — Kleingedrucktes,
	das man vor dem Absenden des Briefes noch einmal liest.

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

	@include('partials.checkout-steps')
	@include('partials.checkout-summary')

	@if($reference === null)
		<section class="account-section checkout-cash">
			<h2 class="account-section__heading">@lang('checkout.cash.how_heading')</h2>
			<p class="account-section__lede">@lang('checkout.cash.description')</p>

			{{--
				Die Anschrift, schon bevor es eine Zahlungs-ID gibt. Sie ist
				keine Auskunft über diesen Auftrag, sondern immer dieselbe
				(lang/*/checkout.php's cash.order.address) — es gibt keinen
				Grund, sie hinter der Zustimmung zu verstecken, und einen
				guten, sie davor zu zeigen: wer 10 € in einen Umschlag legen
				soll, entscheidet das anhand von „wohin“, nicht anhand von
				„wie heißt der Knopf“.
			--}}
			<div class="checkout-cash__address-block">
				<h3 class="checkout-cash__address-label">@lang('checkout.cash.address_label')</h3>
				<p class="checkout-cash__address">{{ __('checkout.cash.order.address') }}</p>
			</div>

			<details class="checkout-cash__notes-details">
				<summary>@lang('checkout.cash.note')</summary>
				<ul class="checkout-cash__notes">
					<li>@lang('checkout.cash.no_large_values')</li>
					<li>@lang('checkout.cash.no_coins')</li>
					<li>@lang('checkout.cash.accepted_currencies')</li>
					<li>@lang('checkout.cash.currency_translation')</li>
					<li>@lang('checkout.cash.no_refund')</li>
				</ul>
			</details>

			@if($error === 'unreachable')
				<p class="checkout-consent__error" role="alert">@lang('checkout.cash.error.unreachable')</p>
			@elseif($error === 'consent')
				<p class="checkout-consent__error" role="alert">@lang('checkout.consent.error')</p>
			@endif

			<form method="post" action="{{ route('account.checkout.cash.submit', ['amount' => $amount]) }}">
				@include('partials.checkout-consent')
				<button type="submit" class="account-btn account-btn--primary checkout-submit">@lang('checkout.cash.generate')</button>
			</form>
		</section>
	@else
		{{--
			Der angelegte Auftrag. Die Zahlungs-ID zuerst und allein in ihrer
			eigenen Karte — sie ist das, was auf den Zettel im Umschlag muss,
			und alles andere auf dieser Seite ist Begleitung dazu.
		--}}
		<section class="account-section checkout-cash">
			<h2 class="account-section__heading">@lang('checkout.cash.order.heading')</h2>
			<p class="account-section__lede">@lang('checkout.cash.description')</p>

			<div class="checkout-cash__order">
				<div class="checkout-cash__order-id">
					<input type="text" id="checkout-order-id" value="{{ $reference['public_id'] }}" readonly
						autocomplete="off" spellcheck="false" data-1p-ignore="true" data-lpignore="true"
						data-form-type="other" data-bwignore>
					<button class="account-save__button" type="button" data-copies="checkout-order-id"
						data-done="@lang('key-create.copy.done')" hidden>@lang('checkout.cash.order.copy')</button>
				</div>
				<p class="checkout-cash__hint">@lang('checkout.cash.order.expiration', ['date' => $reference['expiration']->isoFormat('L')])</p>
				<p class="checkout-cash__hint">@lang('checkout.cash.order.unique')</p>
			</div>

			<div class="checkout-cash__address-block">
				<h3 class="checkout-cash__address-label">@lang('checkout.cash.order.address_heading')</h3>
				<p class="checkout-cash__address">{{ __('checkout.cash.order.address') }}</p>
			</div>

			{{--
				Die Hinweise stehen in *beiden* Zuständen — sie sagen, was in
				den Brief gehört und was danach geschieht (checkout.cash.
				no_refund: sobald die Ladung verbucht ist, gibt es über die
				Auftragsnummer eine Übersicht und eine Rechnung). Der Port
				hatte sie nur im Formular-Zustand gezeigt; nach dem Erzeugen
				der Nummer war die einzige Stelle, die je erklärt, wie der
				Brief aussehen muss, wieder verschwunden. Hier offen und nicht
				zugeklappt wie im Formular-Zustand: jetzt ist der Brief die
				nächste Handlung, nicht mehr die Entscheidung.
			--}}
			<div>
				<h3 class="checkout-cash__notes-heading">@lang('checkout.cash.note')</h3>
				<ul class="checkout-cash__notes">
					<li>@lang('checkout.cash.no_large_values')</li>
					<li>@lang('checkout.cash.no_coins')</li>
					<li>@lang('checkout.cash.accepted_currencies')</li>
					<li>@lang('checkout.cash.currency_translation')</li>
					<li>@lang('checkout.cash.no_refund')</li>
				</ul>
			</div>
		</section>
	@endif

	<nav class="checkout-nav">
		<a class="checkout-back" href="{{ route('account.checkout', ['amount' => $amount]) }}">@lang('checkout.page.methods.back')</a>
		<a class="checkout-back" href="{{ $cancelUrl }}">@lang('checkout.page.cancel')</a>
	</nav>
</div>
@endsection
