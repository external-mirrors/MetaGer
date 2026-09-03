@extends('layouts.subPages', ['page' => 'anmelden'])

@section('title', $title)

@section('content')
{{--
	Bei MetaGer anmelden.

	Lag als /keys/key/enter im Keymanager. Das Formular schickt inzwischen
	hierher zurück — an dieselbe Adresse, unter der die Seite steht; nur die
	Frage, was eine Eingabe eigentlich ist, geht noch an den Keyserver.
	App\Http\Controllers\LoginController hat die Gründe.

	Die Seite kommt ohne Javascript aus. Getippter Schlüssel, Anmeldecode und
	Sicherungsdatei sind einfache Formularfelder; die Gruppierung der Ziffern,
	die Rückfrage bei leerem Schlüssel und der Kamera-Scanner sind Zugaben.
	Der Scanner ist die einzige Bedienung, die ohne Javascript gar nicht geht —
	deshalb steht er hier `hidden` und resources/js/login.js deckt ihn auf.
--}}
<div id="login-page">
	<h1 class="page-title">@lang('login.heading')</h1>
	<p class="login-lede">@lang('login.lede')</p>

	<form id="login-form" class="login-card" method="post" action="{{ $action }}"
		enctype="multipart/form-data" data-charge-endpoint="{{ $chargeEndpoint }}">
		@if($keyError)
			<p class="login-card__error" role="alert">@lang("login.errors.$keyError")</p>
		@endif

		<div class="login-field">
			<label class="login-field__label" for="login-key">@lang('login.key.label')</label>
			{{--
				type="text" und nicht type="password": die alte Seite maskierte das
				Feld und deckte es per Javascript beim Fokus wieder auf — ohne
				Javascript tippte man seinen Schlüssel also blind. Ein Schlüssel wird
				von einem Zettel oder einem zweiten Bildschirm abgelesen; ihn dabei
				nicht sehen zu können ist der sichere Weg zum Tippfehler.

				autocomplete="current-password" und nicht "off": das alte Feld im
				Keymanager war type="password", also bot jeder Passwortspeicher von
				sich aus an, den Schlüssel abzulegen und beim nächsten Mal wieder
				einzusetzen. Mit "off" fiel das weg — die Rückmeldung, die diese
				Zeile wiederherstellt. Der Token genügt Bitwarden, KeePassXC und
				1Password; der browsereigene Speicher will zusätzlich ein echtes
				Passwortfeld sehen, deshalb macht resources/js/login/maskKeyField.js
				daraus wieder type="password" (und deckt es beim Fokus auf), sobald
				Javascript läuft. Ohne Javascript trägt der Token allein.
			--}}
			<input class="login-field__input" type="text" name="key" id="login-key"
				value="{{ $prefill }}" placeholder="@lang('login.key.placeholder')"
				autocomplete="current-password" autocapitalize="off" autocorrect="off" spellcheck="false"
				aria-describedby="login-key-hint" autofocus>
			<p class="login-field__hint" id="login-key-hint">@lang('login.key.hint')</p>
			{{--
				Leer und hidden: resources/js/login.js füllt es aus den data-Attributen.
				Die Meldungen stehen hier und nicht im Javascript, weil sie übersetzt
				sind — role="status" statt "alert", denn sie beschreiben eine Eingabe,
				die noch niemand abgeschickt hat.
			--}}
			<p class="login-field__message" id="login-key-message" role="status" hidden
				data-illegal="@lang('login.validation.hex')"
				data-malformed="@lang('login.validation.uuid')"
				data-incomplete="@lang('login.validation.login')"></p>
		</div>

		<button class="login-submit" type="submit">@lang('login.submit')</button>

		<div class="login-divider"><span>@lang('login.or')</span></div>

		<div class="login-alternatives">
			<div class="login-alternative">
				{{--
					Das Feld selbst, sichtbar, statt eines versteckten Feldes mit einem
					Label davor: nur dann nennt der Browser die gewählte Datei von sich
					aus. Die Variante mit Label brauchte dafür Javascript, und ohne
					Javascript wählte man eine Datei und sah nirgends, welche.
				--}}
				<label class="login-alternative__label" for="login-file">@lang('login.file.button')</label>
				<input class="login-alternative__file" type="file" name="file" id="login-file"
					accept="image/*" aria-describedby="login-file-hint">
				<p class="login-alternative__hint" id="login-file-hint">@lang('login.file.hint')</p>
			</div>

			<div class="login-alternative" id="login-qr" hidden>
				{{--
					Kein eigenes Label darüber wie beim Dateifeld: der Knopf trägt
					seine Beschriftung selbst. Das Dateifeld braucht eines, weil die
					Beschriftung seines Knopfes vom Browser kommt und „Durchsuchen“
					heißt.
				--}}
				<button class="login-alternative__button" type="button" id="login-qr-open"
					aria-describedby="login-qr-hint">@lang('login.qr.button')</button>
				<p class="login-alternative__hint" id="login-qr-hint">@lang('login.qr.hint')</p>
				<p class="login-alternative__message" id="login-qr-message" role="status" hidden
					data-no-camera="@lang('login.qr.no_camera')"
					data-invalid="@lang('login.qr.invalid')"></p>
			</div>
		</div>

		{{--
			Was einen Fehlversuch überleben muss und nirgends sonst steht.
			Versteckte Felder und kein Serverzustand: Webrouten haben keine
			Session.

			Ein `redirect_error` steht nicht mehr dabei. Den brauchte der
			Keymanager, um den Besucher wieder hierher zu schicken; das Ziel
			eines Fehlversuchs ist jetzt diese Seite selbst.
		--}}
		@if($redirectSuccess !== null)
			<input type="hidden" name="redirect_success" value="{{ $redirectSuccess }}">
		@endif
		@foreach($callback as $name => $value)
			<input type="hidden" name="{{ $name }}" value="{{ $value }}">
		@endforeach
	</form>

	<p class="login-create">
		@lang('login.create.prompt')
		<a href="{{ $createUrl }}">@lang('login.create.action')</a>
	</p>

	{{--
		id="plugin-btn" ist der Griff, an dem die Web-Erweiterung diesen Kasten
		entfernt, wenn sie schon installiert ist
		(build/js/contentScripts/removeUnusedContent.js im Erweiterungs-Repo).
		Sie entfernt genau ein Element dieser id — hier ist es dieses.
	--}}
	<aside class="login-extension" id="plugin-btn">
		<h2 class="login-extension__heading">@lang('login.extension.heading')</h2>
		<p class="login-extension__text">{!! trans('login.extension.text', ['tokenlink' => $tokenUrl]) !!}</p>
		<a class="login-extension__action" href="{{ $extension['url'] }}" rel="noopener">{{ $extension['label'] }}</a>
	</aside>
</div>

{{--
	Beide Dialoge stehen außerhalb des Formulars: ein <dialog> im Formular
	schließt bei Escape dessen Absenden nicht aus, und der Scanner deckt die
	ganze Seite ab. Beide sind ohne Javascript nie zu sehen.
--}}
<dialog class="login-dialog" id="login-empty-key">
	<form method="dialog" class="login-dialog__form">
		<p class="login-dialog__message">@lang('login.empty_key.message')</p>
		<p class="login-dialog__value">
			<span class="login-dialog__value-label">@lang('login.empty_key.entered')</span>
			<span id="login-empty-key-value"></span>
		</p>
		<div class="login-dialog__actions">
			<button class="login-dialog__button" type="submit" value="cancel">@lang('login.empty_key.revalidate')</button>
			<button class="login-dialog__button login-dialog__button--primary" type="submit" value="confirm">@lang('login.empty_key.confirm')</button>
		</div>
	</form>
</dialog>

<dialog class="login-scanner" id="login-scanner">
	<video class="login-scanner__video" muted playsinline></video>
	<button class="login-scanner__close" type="button">@lang('login.qr.close')</button>
</dialog>
@endsection
