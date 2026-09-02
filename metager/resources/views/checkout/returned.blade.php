@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Die Landeseite für eine weiterleitende Zahlungsart — micropayment, VR
	Payment und PayPal teilen sie sich
	(App\Http\Controllers\ChargeController::returned()).

	Kein #checkout-page-Rahmen: dieser Vorgang ist hier zu Ende, nicht mitten
	drin, deshalb auch keine Paketkurzfassung, keine Schrittleiste und kein
	"andere Zahlungsart" — nur, was passiert ist, und der Weg weiter.

	**Der neue Kontostand steht hier, groß.** Die Seite nannte nur die
	gekaufte Menge („Ihr Schlüssel wurde um 1.000 Token aufgeladen“) und
	verschwieg damit ausgerechnet das Ergebnis: was jetzt auf dem Schlüssel
	liegt. Dieselbe Kachel wie auf /konto (.account-balance), damit die Zahl,
	die man gerade gekauft hat, dort wiedererkennbar ist, wo man sie später
	nachsieht. $balance ist null, wenn der Kontostand die Gutschrift noch
	nicht enthält (ChargeController::creditedBalance()) — dann fällt nur die
	Zahl weg, der Dank und der Weg weiter bleiben.

	**Der unbezahlte Zustand trägt seine eigene Überschrift.** „Aufladen
	abgeschlossen“ stand vorher auch über „Ihre Zahlung wird noch bearbeitet“
	— ein Widerspruch genau an der Stelle, an der jemand nachsieht, ob seine
	Zahlung angekommen ist.

	$paid ist kein Beleg, nur ob drüben schon eine Payment-Zeile steht — bei
	einer Weiterleitung kann das Webhook-Ereignis den Browser überholen oder
	umgekehrt.
--}}
<div id="account-page">
	<header class="account-head">
		<h1 class="page-title">@lang($paid ? 'checkout.returned.heading' : 'checkout.returned.pending_heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	{{--
		Ein Kasten, nicht zwei. Guthaben, Dank und der Weg weiter gehören zu
		einer einzigen Aussage — „das hier ist jetzt so" —, und derselbe
		Kasten steht auf /konto, wo man diese Zahl später wiedersieht.
	--}}
	<section class="account-balance">
		@if($paid)
			@if($balance !== null)
				<p class="account-balance__figure">
					<span class="account-balance__number">{{ \Illuminate\Support\Number::format(floor($balance), locale: app()->getLocale()) }}</span>
					<span class="account-balance__unit">@lang('account.page.balance.unit')</span>
				</p>
			@endif
			<p class="account-balance__note account-balance__note--strong">@lang('checkout.returned.paid', ['amount' => \Illuminate\Support\Number::format($amount, locale: app()->getLocale())])</p>
			<p class="account-balance__note">@lang('checkout.returned.next')</p>
		@else
			<p class="account-blocked">@lang('checkout.returned.pending')</p>
		@endif

		{{--
			Nach geglückter Zahlung ist "suchen" der nächste Schritt, nicht
			"zurück zum Konto" — deshalb steht die Suche vorn und primär. Solange
			die Zahlung noch bearbeitet wird, führt der primäre Weg zum Konto,
			wo der Stand sichtbar wird; die Suche bleibt als ruhiger Zweitweg.
		--}}
		<div class="account-balance__actions">
			@if($paid)
				<a class="account-btn account-btn--primary" href="{{ $startpageUrl }}">@lang('account.page.actions.search')</a>
				<a class="account-btn account-btn--quiet" href="{{ $orderUrl }}">@lang('checkout.returned.details')</a>
				<a class="account-btn account-btn--quiet" href="{{ $accountUrl }}">@lang('checkout.page.cancel')</a>
			@else
				<a class="account-btn account-btn--primary" href="{{ $accountUrl }}">@lang('checkout.page.cancel')</a>
				<a class="account-btn account-btn--quiet" href="{{ $startpageUrl }}">@lang('account.page.actions.search')</a>
			@endif
		</div>
	</section>

</div>
@endsection
