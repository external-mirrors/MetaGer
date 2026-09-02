@extends('layouts.subPages', ['page' => 'konto'])

@section('title', $title)

@section('content')
{{--
	Das Konto.

	Lag als /keys/key/<uuid> im Keymanager. App\Http\Controllers\AccountController
	hat die Gründe für den Umzug; hier steht, warum die Seite so aussieht.

	**Das Guthaben zuerst, und dann eine einzige Spalte.** Die alte Seite begann
	mit einem QR-Code, danach kam der Schlüssel in voller Länge, dann eine Reihe
	aus vier Knöpfen, und erst darunter stand die Zahl, wegen der die meisten
	Menschen die Seite überhaupt aufrufen. Jetzt steht sie oben und groß; alles
	andere ist ein ruhiger Stapel darunter, der auf einem Telefon dieselbe
	Reihenfolge hat wie auf einem Bildschirm. Kein zweites Layout, keine
	Seitenspalte, die unterwegs umklappt.

	**Der Schlüssel steht nicht mehr in voller Länge auf der Seite.** Er stand
	dort als große, anklickbare Zeichenfolge — auf einer Seite, die Menschen in
	Supportanfragen fotografieren. Was jetzt dasteht, sind die letzten sechs
	Zeichen als Kennung, und die Wege, den ganzen Schlüssel aufzubewahren,
	stehen untereinander im Abschnitt „Zugang sichern“.

	**Die Zahlen werden lokalisiert und nicht deutsch formatiert.** `2.480` heißt
	auf Englisch `2,480`, und diese Seite wird in zwölf Sprachen ausgeliefert.
	`Number::format()` mit der Sprache der Anfrage nimmt die Entscheidung ab —
	`number_format()` mit festen Trennzeichen hätte sie stillschweigend für alle
	getroffen.

	**Ohne Javascript fehlen genau zwei Dinge, und beide sind Bequemlichkeiten:**
	die Kopierknöpfe (ein readonly-Feld lässt sich von Hand markieren) und der
	Dialog für ein weiteres Gerät (der Code ist eine Abfrage, die es ohne Skript
	nicht gibt). Alles andere — Guthaben, Verfallsdaten, Pakete, QR-Code,
	Lesezeichen — steht im Markup.
--}}
<div id="account-page">
	<header class="account-head">
		<h1 class="page-title">@lang('account.page.heading')</h1>
		@include('partials.key-fingerprint')
	</header>

	{{--
		Die Zahl, wegen der die Seite aufgerufen wird. `account-balance--empty`
		und `--low` färben den Rahmen, nicht die Ziffern: die Zahl soll in jedem
		Zustand gleich gut lesbar sein.
	--}}
	<section class="account-balance @if($unreachable) account-balance--unknown @else account-balance--{{ strtolower($state->name) }} @endif">
		@if($unreachable)
			<p class="account-balance__unknown" role="alert">@lang('account.page.balance.unknown')</p>
		@else
			<p class="account-balance__figure">
				<span class="account-balance__number">{{ \Illuminate\Support\Number::format(floor($charge), locale: app()->getLocale()) }}</span>
				<span class="account-balance__unit">@lang('account.page.balance.unit')</span>
			</p>

			@if($charge <= 0)
				<p class="account-balance__note account-balance__note--empty">@lang('account.page.balance.empty')</p>
			@else
				@if($state === \App\Authentication\KeyState::LOW)
					<p class="account-balance__note account-balance__note--low">@lang('account.page.balance.low')</p>
				@endif
				<p class="account-balance__note">@lang('account.page.balance.one_token')</p>
			@endif

			{{--
				Nur solange es ein Guthaben gibt, das gültig sein kann. Bei null
				rechnet der Keyserver weiter ein Datum aus — dann ist es die
				Frist, nach der der *Schlüssel* selbst verfällt, und
				„Guthaben gültig bis“ wäre daneben schlicht falsch.
			--}}
			@if($expiration !== null && $charge > 0)
				{{--
					Ein Datum reicht, solange es nur eine Aufladung gibt. Bei
					mehreren verfallen sie nacheinander, und dann ist die eine
					Zahl oben eine Vereinfachung — <details> statt des alten
					Fragezeichens mit Tooltip, weil ein Tooltip auf einem
					Telefon nichts ist, worauf man zeigen kann.
				--}}
				@if(count($orders) > 1)
					<details class="account-expiry">
						<summary class="account-expiry__summary">
							<span class="account-expiry__date">@lang('account.page.balance.valid_until', ['date' => $expiration->isoFormat('L')])</span>
							<span class="account-expiry__hint">@lang('account.page.balance.orders_summary', ['count' => count($orders)])</span>
						</summary>
						<ol class="account-expiry__list">
							@foreach($orders as $order)
								<li>@lang('account.page.balance.order', [
									'amount' => \Illuminate\Support\Number::format(floor($order['amount']), locale: app()->getLocale()),
									'date' => $order['expiration']?->isoFormat('L') ?? '—',
								])</li>
							@endforeach
						</ol>
					</details>
				@else
					<p class="account-expiry account-expiry--single">@lang('account.page.balance.valid_until', ['date' => $expiration->isoFormat('L')])</p>
				@endif
			@endif
		@endif

		<div class="account-balance__actions">
			@if($topupBlocked === null && $tiers !== [])
				<a class="account-btn account-btn--primary" href="#charge">@lang('account.page.actions.topup')</a>
			@endif
			<a class="account-btn account-btn--quiet" href="{{ $searchUrl }}">@lang('account.page.actions.search')</a>
		</div>
	</section>

	{{--
		Aufladen. Die Kacheln sind Links auf die lokale Aufladeseite
		(App\Http\Controllers\ChargeController) — cash und die
		Entwicklungs-Zahlungsart laufen dort inzwischen selbst, alles andere
		verlinkt von dort aus weiter in den Keymanager, bis es nachzieht.
	--}}
	<section class="account-section" id="charge">
		<h2 class="account-section__heading">@lang('account.page.charge.heading')</h2>

		@if($topupBlocked !== null)
			<p class="account-blocked">@lang("account.page.charge.blocked.$topupBlocked")</p>
			@if($topupBlocked === 'member' && \App\Support\MembershipOffer::isAdvertised())
				<a class="account-section__more" href="{{ $membershipUrl }}">@lang('account.page.charge.more')</a>
			@endif
		@elseif($tiers === [])
			<p class="account-blocked">@lang('account.page.balance.unknown')</p>
		@else
			<p class="account-section__lede">@lang('account.page.charge.lede')</p>
			<ul class="account-tiers">
				@foreach($tiers as $amount => $price)
					<li>
						<a class="account-tier" href="{{ route('account.checkout', ['amount' => $amount]) }}">
							<span class="account-tier__amount">@lang('account.page.charge.tokens', ['amount' => \Illuminate\Support\Number::format($amount, locale: app()->getLocale())])</span>
							<span class="account-tier__price">@lang('account.page.charge.price', ['price' => $price])</span>
						</a>
					</li>
				@endforeach
			</ul>
			<a class="account-section__more" href="{{ $priceUrl }}">@lang('account.page.charge.more')</a>

			{{--
				Die Alternative zum Bezahlen, an der Stelle, an der jemand
				bezahlen will. Der Keymanager zeigte den Hinweis auf die
				Mitgliedschaft an genau dieser Stelle; er ist beim Umzug
				verlorengegangen, und er gehört hierher und nicht auf eine
				Werbeseite: Mitglieder suchen ohne weitere Kosten, der
				Schlüssel wird monatlich aus dem Beitrag aufgefüllt. Wer
				gerade ein Token-Paket kaufen will, ist der Einzige, den das
				betrifft.

				Nur auf der deutschen Oberfläche, wie jeder Hinweis auf die
				Mitgliedschaft — siehe App\Support\MembershipOffer.
			--}}
			@if(\App\Support\MembershipOffer::isAdvertised())
				<div class="account-membership" id="charge-membership">
					<h3 class="account-membership__heading">@lang('account.page.charge.membership.heading')</h3>
					<p class="account-membership__text">{!! __('account.page.charge.membership.text') !!}</p>
					<a class="account-membership__action" href="{{ $membershipUrl }}">@lang('account.page.charge.membership.action')</a>
				</div>
			@endif
		@endif
	</section>

	{{--
		Zugang sichern. Die Kernsektion (Schlüssel, QR, Lesezeichen-URL, und —
		nur hier, weil nur das Konto den Anmeldecode kennt — das zweite Gerät)
		steht jetzt in partials/key-backup.blade.php, geteilt mit den
		Aufladeseiten. Dort steht auch, warum die Wege untereinander und nicht
		nebeneinander stehen.
	--}}
	@if($key !== null)
		@include('partials.key-backup', ['loginCodeUrl' => $loginCodeUrl])
	@endif

	<section class="account-section account-more">
		<h2 class="account-section__heading">@lang('account.page.more.heading')</h2>
		<ul class="account-more__list">
			@if($ordersUrl !== null)
				<li><a href="{{ $ordersUrl }}">@lang('account.page.more.orders')</a></li>
			@endif
			@if($campaignsUrl !== null)
				<li><a href="{{ $campaignsUrl }}">@lang('account.page.more.campaigns')</a></li>
			@endif
			<li><a href="{{ $helpUrl }}">@lang('account.page.more.help')</a></li>
			<li class="account-more__logout">
				<a href="{{ $logoutUrl }}" id="account-logout">@lang('account.page.more.logout')</a>
				<span class="account-more__hint">@lang('account.page.more.logout_hint')</span>
			</li>
		</ul>
	</section>
</div>

{{--
	Der Dialog für ein weiteres Gerät. Steht außerhalb der Seitenspalte, weil
	<dialog> im Top-Layer rendert; sein Inhalt wird von resources/js/account.js
	gefüllt und im Sekundentakt nachgeprüft — wechselt der Code, ist der alte
	verbraucht und der Dialog schließt.
--}}
<dialog class="account-dialog" id="account-transfer" data-code-url="{{ $loginCodeUrl }}">
	<h2 class="account-dialog__title">@lang('account.page.save.transfer.title')</h2>
	<p class="account-dialog__text">@lang('account.page.save.transfer.description')</p>
	<p class="account-dialog__code" id="account-transfer-code" aria-live="polite">@lang('account.page.save.transfer.waiting')</p>
	<p class="account-dialog__failed" id="account-transfer-failed" hidden role="alert">@lang('account.page.save.transfer.failed')</p>
	<p class="account-dialog__note">@lang('account.page.save.transfer.note')</p>
	<button class="account-btn account-btn--quiet" type="button" id="account-transfer-close">@lang('account.page.save.transfer.close')</button>
</dialog>
@endsection
