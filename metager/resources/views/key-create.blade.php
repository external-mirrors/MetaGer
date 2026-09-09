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
	wird von dort aufgedeckt (resources/js/key-backup.js): die beiden
	Kopierknöpfe (ein `readonly`-Feld lässt sich von Hand markieren, ein Knopf
	ohne Zwischenablage tut nichts) und der Hinweis für Browser, die keine
	Cookies behalten.
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
				Kennung, Schlüsselfeld und Aufbewahren stehen in parts/key-backup —
				/membership/success zeigt denselben Block. Steht innerhalb von
				.create-result und damit erst nach dem Aufdecken: die Marke ist aus dem
				Schlüssel abgeleitet, und vor der Nachfrage soll von ihm nichts zu sehen
				sein.
			--}}
			@include("parts.key-backup", ["key" => $key, "settingsUrl" => $settingsUrl, "qrUri" => $qrUri])

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
