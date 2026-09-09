{{--
	Den Schlüssel behalten.

	Derselbe Block auf zwei Seiten, die sonst nichts miteinander zu tun haben:
	/schluessel-erstellen zeigt ihn, wenn jemand gerade einen erstellt hat, und
	/membership/success, wenn der Aufnahmeantrag im ersten Schritt still einen
	angelegt hat. Für ein Mitglied ist diese Seite oft die einzige Gelegenheit,
	ihn überhaupt zu sehen — die Willkommensmail nennt ihn erst nach der
	Bearbeitung.

	Erwartet:
	  $key         der Schlüssel
	  $settingsUrl der URL, der ihn samt Sucheinstellungen wieder einrichtet
	  $qrUri       derselbe Weg als Bild, als data:-URI
	  $keyLabel    optional, die Beschriftung des Feldes. Voreingestellt ist
	               „Ihr neuer Schlüssel“ — das stimmt beim Erstellen und nicht
	               auf der Erfolgsseite des Aufnahmeantrags, wo ein bereits
	               angemeldetes Mitglied seinen bestehenden behält.

	Alles, was ohne Javascript nicht geht, steht `hidden` im Markup und wird von
	dort aufgedeckt (resources/js/key-backup.js): die Kopierknöpfe — ein
	`readonly`-Feld lässt sich von Hand markieren, ein Knopf ohne Zwischenablage
	tut nichts — und der Hinweis für Browser, die keine Cookies behalten.

	Die Klassen heißen `keybackup-*`; das Stylesheet dazu ist
	resources/less/metager/key-backup.less. Die *IDs* heißen weiter wie auf der
	Seite zum Erstellen (`new-key`, `key-create-no-cookies`) — an ihnen hängen
	resources/js/key-backup.js und, für `new-key`, auch key-create.js, das den
	Schlüssel für seine Nachfrage wieder wegblendet.
--}}
<p class="keybackup-identity">
	{{--
		Die Kennung des Kontos: die Marke und die letzten sechs Zeichen — genau
		das, was von jetzt an in der Ecke jeder Seite steht
		(parts/account-pill.blade.php). Hier zum ersten Mal, damit sie
		wiedererkannt wird statt beim ersten Auftauchen erklärt werden zu müssen.
	--}}
	{!! \App\Authentication\KeyIdenticon::render(substr($key, -6)) !!}
	<span class="keybackup-identity__code">@lang('account.page.fingerprint', ['fingerprint' => strtoupper(substr($key, -6))])</span>
	<span class="keybackup-identity__hint">@lang('key-create.identity')</span>
</p>

<div class="keybackup-key">
	<label class="keybackup-key__label" for="new-key">{{ $keyLabel ?? __('key-create.key.label') }}</label>
	{{--
		readonly und nicht disabled: ein deaktiviertes Feld lässt sich weder
		markieren noch vorlesen, und beides ist hier genau das, was jemand ohne
		Zwischenablage tun will.
	--}}
	<input class="keybackup-key__input" type="text" id="new-key" name="new-key" value="{{ $key }}"
		readonly autocomplete="off" spellcheck="false"
		aria-describedby="new-key-hint">
	<button class="keybackup-key__copy" type="button" data-copies="new-key"
		data-done="@lang('key-create.copy.done')" hidden>@lang('key-create.copy.action')</button>
	<p class="keybackup-key__hint" id="new-key-hint">@lang('key-create.key.hint')</p>
</div>

<div class="keybackup-save">
	<h2 class="keybackup-save__heading">@lang('key-create.save.heading')</h2>
	<p class="keybackup-save__text">@lang('key-create.save.text')</p>

	<div class="keybackup-save__options">
		<div class="keybackup-save__option">
			{{--
				Bild und Herunterladen sind derselbe data:-URI, einmal angezeigt
				und einmal gespeichert. Eine eigene Route müsste den Schlüssel in
				ihrer Adresse tragen, und das ist der Umweg, den der Umzug des
				Kontos abschafft.
			--}}
			<a class="keybackup-save__qr" href="{{ $qrUri }}" download="metager-schluessel.png">
				<img src="{{ $qrUri }}" alt="@lang('key-create.save.qr.alt')" width="180" height="180">
				<span class="keybackup-save__action">@lang('key-create.save.qr.action')</span>
			</a>
			<p class="keybackup-save__hint">@lang('key-create.save.qr.hint')</p>
		</div>

		<div class="keybackup-save__option">
			<label class="keybackup-save__label" for="restore-url">@lang('key-create.save.url.label')</label>
			<input class="keybackup-save__input" type="text" id="restore-url" name="restore-url"
				value="{{ $settingsUrl }}" readonly autocomplete="off" spellcheck="false">
			<button class="keybackup-save__copy" type="button" data-copies="restore-url"
				data-done="@lang('key-create.copy.done')" hidden>@lang('key-create.save.url.action')</button>
			<p class="keybackup-save__hint">@lang('key-create.save.url.hint')</p>
		</div>
	</div>

	{{--
		Nur mit Javascript zu beantworten: ob dieser Browser ein Cookie
		überhaupt behält. resources/js/key-backup.js probiert es und deckt den
		Absatz auf, wenn nicht.
	--}}
	<p class="keybackup-save__no-cookies" id="key-create-no-cookies" hidden>@lang('key-create.save.no_cookies')</p>
</div>
