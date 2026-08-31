{{--
	Zugang sichern — dieselbe "#save"-Sektion, die vorher nur in
	account.blade.php stand. Herausgezogen, damit die Aufladeseiten sie auch
	zeigen können: gerade beim Bezahlen ist der Moment, an dem ein verlorener
	Schlüssel am teuersten wäre, also ist es der richtige Moment, die
	Sicherung anzubieten.

	Braucht resources/js/account.js (Kopierknöpfe über [data-copies]).

	Erwartet $key, $qrUri, $settingsUrl. Der Aufrufer entscheidet, ob $key
	überhaupt vorhanden ist — siehe die @if-Umklammerung in account.blade.php.

	$loginCodeUrl ist optional: nur das Konto kennt den Anmeldecode-Endpunkt
	und zeigt damit den dritten Weg (zweites Gerät) im selben Raster — dessen
	<dialog> bleibt in account.blade.php selbst, absichtlich außerhalb der
	Seitenspalte (siehe dort). Ohne $loginCodeUrl bleibt es bei den zwei Wegen,
	die kein zweites Gerät voraussetzen — das reicht auf den Aufladeseiten, die
	ohnehin schon zeigen, welcher Schlüssel zahlt
	(partials/key-fingerprint.blade.php).
--}}
<section class="account-section" id="save">
	<h2 class="account-section__heading">@lang('account.page.save.heading')</h2>
	<p class="account-section__lede">@lang('account.page.save.text')</p>

	<details class="account-key">
		<summary class="account-key__summary">@lang('account.page.save.key.summary')</summary>
		<div class="account-key__body">
			<label class="account-key__label" for="account-key">@lang('account.page.save.key.label')</label>
			<input class="account-key__input" type="text" id="account-key" name="account-key"
				value="{{ $key }}" readonly autocomplete="off" spellcheck="false"
				aria-describedby="account-key-hint"
				data-1p-ignore="true" data-lpignore="true" data-form-type="other" data-bwignore>
			<button class="account-save__button" type="button" data-copies="account-key"
				data-done="@lang('key-create.copy.done')" hidden>@lang('account.page.save.key.action')</button>
			<p class="account-key__hint" id="account-key-hint">@lang('account.page.save.key.hint')</p>
		</div>
	</details>

	<div class="account-save">
		<div class="account-save__option">
			<h3 class="account-save__label">@lang('account.page.save.qr.label')</h3>
			<a class="account-save__qr" href="{{ $qrUri }}" download="metager-schluessel.png">
				<img src="{{ $qrUri }}" alt="@lang('account.page.save.qr.alt')" width="140" height="140">
				<span class="account-save__action">@lang('account.page.save.qr.action')</span>
			</a>
			<p class="account-save__hint">@lang('account.page.save.qr.hint')</p>
		</div>

		<div class="account-save__option">
			<h3 class="account-save__label"><label for="restore-url">@lang('account.page.save.url.label')</label></h3>
			<input class="account-save__input" type="text" id="restore-url" name="restore-url"
				value="{{ $settingsUrl }}" readonly autocomplete="off" spellcheck="false"
				data-1p-ignore="true" data-lpignore="true" data-form-type="other" data-bwignore>
			<button class="account-save__button" type="button" data-copies="restore-url"
				data-done="@lang('key-create.copy.done')" hidden>@lang('account.page.save.url.action')</button>
			<p class="account-save__hint">@lang('account.page.save.url.hint')</p>
		</div>

		@isset($loginCodeUrl)
			<div class="account-save__option">
				<h3 class="account-save__label">@lang('account.page.save.transfer.label')</h3>
				{{--
					Ohne Skript gibt es keinen Code — er ist zehn Sekunden gültig
					und müsste sonst bei jedem Seitenaufruf neu geholt werden,
					auch von jemandem, der ihn gar nicht will. Der Knopf bleibt
					deshalb verborgen, bis resources/js/account.js ihn aufdeckt.
				--}}
				<button class="account-save__button account-save__button--transfer" type="button"
					id="account-transfer-open" hidden>@lang('account.page.save.transfer.action')</button>
				<p class="account-save__hint">@lang('account.page.save.transfer.hint')</p>
			</div>
		@endisset
	</div>
</section>
