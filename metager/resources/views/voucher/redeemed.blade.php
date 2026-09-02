@extends('layouts.subPages', ['page' => 'c'])

@section('title', $title)

@section('content')
{{--
	Einen Gutschein einlösen — der Schlüssel.

	Lag als /keys/c im Keymanager (views/campaign/redeemed.ejs). Die Seite ist
	die Zwillingsschwester des Ergebnisblocks auf /schluessel-erstellen
	(key-create.blade.php) und benutzt bewusst dieselben Griffe — dieselbe
	Kennung, dasselbe `readonly`-Feld mit Kopierknopf daneben, denselben
	QR-Code als data:-URI, denselben Hinweis für Browser ohne Cookies.

	**Hier ist der Schlüssel dringender als dort.** Wer einen Schlüssel
	erstellt, hat sich dafür entschieden und weiß, was er in der Hand hält; wer
	einen Gutschein einlöst, bekommt einen, den er nirgends abgeholt hat. Er
	steht deshalb vor allem anderen, und der Weg zur Suche kommt erst danach.

	Ohne Javascript ist alles da: Schlüssel, QR-Code, Adresse zum Aufheben und
	beide Links. `hidden` steht nur an dem, was ohne Skript nichts täte — die
	beiden Kopierknöpfe (ein `readonly`-Feld lässt sich von Hand markieren) und
	die Cookie-Warnung, die eine Frage ist, die nur der Browser beantworten
	kann. resources/js/voucher.js deckt beides auf.

	Die Beschriftungen der beiden Felder und die des Kopiervermerks kommen aus
	`key-create` und nicht aus `campaigns`: es sind wortgleich dieselben Felder
	mit demselben Inhalt, und zwei Übersetzungen desselben Wortes gehen in zwölf
	Sprachen irgendwann auseinander. campaigns/index.blade.php greift für
	`key-create.copy.done` schon genauso hinüber.
--}}
@php
	$number = fn (int|float $value) => \Illuminate\Support\Number::format($value, locale: app()->getLocale());
@endphp
<div id="voucher-page" class="voucher-page--redeemed">
	<h1 class="page-title">@lang('campaigns.redeem.redeemed.heading')</h1>
	<p class="voucher-lede">@lang('campaigns.redeem.redeemed.description', ['tokens' => $number($tokens)])</p>

	<div class="voucher-card">
		{{--
			Die Kennung des neuen Kontos: die Marke und die letzten sechs
			Zeichen — genau das, was von jetzt an in der Ecke jeder Seite steht
			(parts/account-pill.blade.php). Hier zum ersten Mal, damit sie
			wiedererkannt wird, statt beim ersten Auftauchen erklärt werden zu
			müssen.
		--}}
		<p class="voucher-identity">
			{!! \App\Authentication\KeyIdenticon::render(strtolower($fingerprint)) !!}
			<span class="voucher-identity__code">@lang('account.page.fingerprint', ['fingerprint' => $fingerprint])</span>
		</p>

		<section class="voucher-step">
			<h2 class="voucher-step__heading">@lang('campaigns.redeem.redeemed.save.heading')</h2>
			<p class="voucher-step__text">@lang('campaigns.redeem.redeemed.save.description')</p>

			<div class="voucher-key">
				<label class="voucher-key__label" for="voucher-new-key">@lang('key-create.key.label')</label>
				{{--
					readonly und nicht disabled: ein deaktiviertes Feld lässt
					sich weder markieren noch vorlesen, und beides ist hier
					genau das, was jemand ohne Zwischenablage tun will.
				--}}
				<input class="voucher-key__input" type="text" id="voucher-new-key" name="new-key"
					value="{{ $key }}" readonly autocomplete="off" spellcheck="false">
				<button class="voucher-key__copy" type="button" data-copies="voucher-new-key"
					data-done="@lang('key-create.copy.done')" hidden>@lang('campaigns.redeem.redeemed.copy_key')</button>
			</div>

			{{--
				Bild und Herunterladen sind derselbe data:-URI, einmal
				angezeigt und einmal gespeichert. Eine eigene Route müsste den
				Schlüssel in ihrer Adresse tragen, und das ist der Umweg, den
				dieser Umzug abschafft. Der Alternativtext ist zugleich die
				Beschriftung des Links — es gibt nichts daneben, das ihn
				beschriften könnte.
			--}}
			<a class="voucher-qr" href="{{ $qrUri }}" download="metager-schluessel.png">
				<img src="{{ $qrUri }}" alt="@lang('campaigns.redeem.redeemed.qr_alt')" width="180" height="180">
			</a>

			<p class="voucher-hint">@lang('campaigns.redeem.redeemed.validity', ['date' => $expiration])</p>

			{{--
				Nur mit Javascript zu beantworten: ob dieser Browser ein Cookie
				überhaupt behält. Wer hier keines behält, hat gleich einen
				Schlüssel, den er nirgends wiederfindet.
			--}}
			<p class="voucher-no-cookies" id="voucher-no-cookies" hidden>@lang('campaigns.redeem.redeemed.no_cookies')</p>
		</section>

		<section class="voucher-step">
			<h2 class="voucher-step__heading">@lang('campaigns.redeem.redeemed.use.heading')</h2>
			<p class="voucher-step__text">@lang('campaigns.redeem.redeemed.use.description')</p>

			<div class="voucher-url">
				<label class="voucher-url__label" for="voucher-settings-url">@lang('key-create.save.url.label')</label>
				<input class="voucher-url__input" type="text" id="voucher-settings-url" name="settings-url"
					value="{{ $settingsUrl }}" readonly autocomplete="off" spellcheck="false">
				<button class="voucher-url__copy" type="button" data-copies="voucher-settings-url"
					data-done="@lang('key-create.copy.done')" hidden>@lang('campaigns.redeem.redeemed.copy_url')</button>
			</div>

			{{--
				Derselbe URL als Link daneben: das Feld ist zum Aufheben, der
				Knopf ist zum Losgehen. Ohne den Link müsste jemand die Adresse
				markieren und in seine eigene Adresszeile tragen, um genau da
				zu landen, wo er ohnehin schon ist.
			--}}
			<div class="voucher-actions">
				<a class="voucher-actions__primary" href="{{ $settingsUrl }}">@lang('campaigns.redeem.redeemed.start_searching')</a>
				<a class="voucher-actions__quiet" href="{{ $accountUrl }}">@lang('campaigns.redeem.redeemed.to_account')</a>
			</div>
		</section>
	</div>
</div>
@endsection
