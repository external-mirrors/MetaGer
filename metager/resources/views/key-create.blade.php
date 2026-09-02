@extends('layouts.subPages', ['page' => 'schluessel-erstellen'])

@section('title', $title)

@section('content')
{{--
	Einen Schlüssel erstellen.

	Lag als /keys/key/create im Keymanager. App\Http\Controllers\KeyCreationController
	hat die Gründe für den Umzug; hier steht, warum die Seite so aussieht.

	**Ohne Javascript ist der Schlüssel schon da.** Das Markup zeigt ihn, das
	Formular darunter nimmt ihn an, und beides funktioniert ohne eine Zeile
	Skript. resources/js/key-create.js dreht das um: es blendet den Schlüssel
	weg und stellt einen Knopf davor. Der Knopf ist die Nachfrage — wer sein
	Cookie verloren hat, hat kein Konto verloren, und ein zweiter Schlüssel
	bekommt ein eigenes, getrenntes Guthaben.

	Alles, was ohne Javascript nicht geht, steht deshalb `hidden` im Markup und
	wird von dort aufgedeckt: die beiden Kopierknöpfe (ein `readonly`-Feld lässt
	sich von Hand markieren, ein Knopf ohne Zwischenablage tut nichts) und der
	Hinweis für Browser, die keine Cookies behalten.
--}}
<div id="key-create-page">
	<h1 class="page-title">@lang('key-create.heading')</h1>
	<p class="create-lede">@lang('key-create.lede')</p>

	@if($keyError)
		<p class="create-error" role="alert">@lang("key-create.errors.$keyError")</p>
	@endif

	{{--
		Vor der Karte und nicht darin: die Frage kommt vor der Handlung. Der
		Support hört regelmäßig von Menschen, die ihr Cookie verloren, hier einen
		zweiten Schlüssel erstellt und dann ihr Guthaben gesucht haben.
	--}}
	<aside class="create-existing">
		<p class="create-existing__text">@lang('key-create.existing.text')</p>
		<a class="create-existing__action" href="{{ $loginUrl }}">@lang('key-create.existing.action')</a>
	</aside>

	@if($key !== null)
	<div class="create-card" id="key-create" data-state="ready">
		{{--
			Der Zustand vor dem Schlüssel. Ohne Javascript blendet die CSS-Regel
			zu data-state="ready" ihn aus — er ist dann nichts als ein Knopf, der
			zeigt, was ohnehin schon dasteht.
		--}}
		<div class="create-offer">
			<p class="create-offer__text">@lang('key-create.offer.text')</p>
			<button class="create-offer__button" type="button" id="key-create-start">@lang('key-create.offer.button')</button>
		</div>

		<p class="create-working">@lang('key-create.working')</p>

		<div class="create-result">
			{{--
				Die Kennung des neuen Kontos: die Marke und die letzten sechs
				Zeichen — genau das, was von jetzt an in der Ecke jeder Seite
				steht (parts/account-pill.blade.php) und was das Konto selbst
				oben zeigt. Hier zum ersten Mal, damit sie wiedererkannt wird
				statt beim ersten Auftauchen erklärt werden zu müssen.

				Steht innerhalb von .create-result und damit erst nach dem
				Aufdecken: die Marke ist aus dem Schlüssel abgeleitet, und vor
				der Nachfrage soll von ihm nichts zu sehen sein.
			--}}
			<p class="create-identity">
				{!! \App\Authentication\KeyIdenticon::render(substr($key, -6)) !!}
				<span class="create-identity__code">@lang('account.page.fingerprint', ['fingerprint' => strtoupper(substr($key, -6))])</span>
				<span class="create-identity__hint">@lang('key-create.identity')</span>
			</p>

			<div class="create-key">
				<label class="create-key__label" for="new-key">@lang('key-create.key.label')</label>
				{{--
					readonly und nicht disabled: ein deaktiviertes Feld lässt sich
					weder markieren noch vorlesen, und beides ist hier genau das,
					was jemand ohne Zwischenablage tun will.
				--}}
				<input class="create-key__input" type="text" id="new-key" name="new-key" value="{{ $key }}"
					readonly autocomplete="off" spellcheck="false"
					aria-describedby="new-key-hint">
				<button class="create-key__copy" type="button" data-copies="new-key"
					data-done="@lang('key-create.copy.done')" hidden>@lang('key-create.copy.action')</button>
				<p class="create-key__hint" id="new-key-hint">@lang('key-create.key.hint')</p>
			</div>

			<div class="create-save">
				<h2 class="create-save__heading">@lang('key-create.save.heading')</h2>
				<p class="create-save__text">@lang('key-create.save.text')</p>

				<div class="create-save__options">
					<div class="create-save__option">
						{{--
							Bild und Herunterladen sind derselbe data:-URI, einmal
							angezeigt und einmal gespeichert. Eine eigene Route
							müsste den Schlüssel in ihrer Adresse tragen, und das
							ist der Umweg, den dieser Umzug abschafft.
						--}}
						<a class="create-save__qr" href="{{ $qrUri }}" download="metager-schluessel.png">
							<img src="{{ $qrUri }}" alt="@lang('key-create.save.qr.alt')" width="180" height="180">
							<span class="create-save__action">@lang('key-create.save.qr.action')</span>
						</a>
						<p class="create-save__hint">@lang('key-create.save.qr.hint')</p>
					</div>

					<div class="create-save__option">
						<label class="create-save__label" for="restore-url">@lang('key-create.save.url.label')</label>
						<input class="create-save__input" type="text" id="restore-url" name="restore-url"
							value="{{ $settingsUrl }}" readonly autocomplete="off" spellcheck="false">
						<button class="create-save__copy" type="button" data-copies="restore-url"
							data-done="@lang('key-create.copy.done')" hidden>@lang('key-create.save.url.action')</button>
						<p class="create-save__hint">@lang('key-create.save.url.hint')</p>
					</div>
				</div>

				{{--
					Nur mit Javascript zu beantworten: ob dieser Browser ein Cookie
					überhaupt behält. resources/js/key-create.js probiert es und
					deckt den Absatz auf, wenn nicht.
				--}}
				<p class="create-save__no-cookies" id="key-create-no-cookies" hidden>@lang('key-create.save.no_cookies')</p>
			</div>

			<form class="create-continue" method="post" action="{{ $action }}">
				{{--
					Der Schlüssel geht als verstecktes Feld zurück, weil es keine
					Session gibt, in der er zwischen den beiden Anfragen stehen
					könnte. Dasselbe gilt für die Callback-Marker der MetaGer-App:
					ohne sie kommt der Schlüssel nie in der App an, und niemand
					sieht, warum.
				--}}
				<input type="hidden" name="key" value="{{ $key }}">
				@foreach($callback as $name => $value)
					<input type="hidden" name="{{ $name }}" value="{{ $value }}">
				@endforeach
				<button class="create-continue__button" type="submit">@lang('key-create.continue')</button>
				<p class="create-continue__hint">@lang('key-create.continue_hint')</p>
			</form>
		</div>
	</div>
	@endif
</div>
@endsection
