{{--
	Zugang sichern — die "#save"-Sektion des Kontos.

	Ein eigenes Partial und kein Stück von account.blade.php, weil der vierte
	Weg optional ist: die Sektion muss auch dreiwegig richtig aussehen. Heute
	bindet nur account.blade.php sie ein; die Aufladeseiten lassen sie bewusst
	weg ({@see \App\Http\Controllers\ChargeController} sagt, warum).

	Braucht resources/js/account.js (Kopierknöpfe über [data-copies]).

	Erwartet $key, $qrUri, $settingsUrl. Der Aufrufer entscheidet, ob $key
	überhaupt vorhanden ist — siehe die @if-Umklammerung in account.blade.php.

	$loginCodeUrl ist optional: nur das Konto kennt den Anmeldecode-Endpunkt
	und zeigt damit den vierten Weg (zweites Gerät) — dessen <dialog> bleibt in
	account.blade.php selbst, absichtlich außerhalb der Seitenspalte (siehe
	dort). Ohne $loginCodeUrl bleibt es bei den drei Wegen, die kein zweites
	Gerät voraussetzen.

	**Ein Stapel und kein Raster.** Die vier Wege standen als gleich große
	Kacheln nebeneinander in `repeat(auto-fit, minmax(12rem, 1fr))`. Das kostete
	dreierlei: bei drei Spalten fiel der vierte Weg allein in eine zweite Reihe
	und ließ die halbe Breite leer; jeder Hinweissatz wurde in einer 12rem
	schmalen Spalte zu vier flatternden Zeilen, zusammen sechzehn; und der
	Schlüssel selbst — der Grund, aus dem es diesen Abschnitt gibt — war einer
	von vier Gleichen und im Feld hinter Zeichen zwanzig abgeschnitten. Jetzt
	ist es eine Spalte, durch Haarlinien getrennt: die Hinweise stehen über die
	volle Breite und werden ein bis zwei Zeilen, der Schlüssel steht ganz da,
	und die Reihenfolge ist dieselbe, die ein Telefon ohnehin erzwingt.

	Die Reihenfolge ist die des abnehmenden Nutzens: der Schlüssel, dann
	derselbe Schlüssel als Bild, dann die beiden Wege, die etwas anderes
	mitbringen — der Lesezeichen-URL auch die Sucheinstellungen, das zweite
	Gerät einen kurzen Code statt sechsunddreißig Zeichen.
--}}
<section class="account-section" id="save">
	<h2 class="account-section__heading">@lang('account.page.save.heading')</h2>
	<p class="account-section__lede">@lang('account.page.save.text')</p>

	<div class="account-save">
		<div class="account-save__option">
			<h3 class="account-save__label"><label for="account-key">@lang('account.page.save.key.label')</label></h3>
			<div class="account-save__control">
				<input class="account-save__input account-save__input--key" type="text" id="account-key" name="account-key"
					value="{{ $key }}" readonly autocomplete="off" spellcheck="false"
					aria-describedby="account-key-hint"
					data-1p-ignore="true" data-lpignore="true" data-form-type="other" data-bwignore>
				<button class="account-save__button" type="button" data-copies="account-key"
					data-done="@lang('key-create.copy.done')" hidden>@lang('account.page.save.key.action')</button>
			</div>
			<p class="account-save__hint" id="account-key-hint">@lang('account.page.save.key.hint')</p>
		</div>

		{{--
			Derselbe Schlüssel als Bild. Das Bild steht rechts und der Text
			links, in DOM-Reihenfolge: Überschrift, Satz, dann der Link, der
			das Bild *und* seine Beschriftung umschließt — ein Ziel, ein Link.
			Unter @screen-mobile-l fällt es untereinander.
		--}}
		<div class="account-save__option account-save__option--qr">
			<h3 class="account-save__label">@lang('account.page.save.qr.label')</h3>
			<p class="account-save__hint">@lang('account.page.save.qr.hint')</p>
			<a class="account-save__qr" href="{{ $qrUri }}" download="metager-schluessel.png">
				<img src="{{ $qrUri }}" alt="@lang('account.page.save.qr.alt')" width="140" height="140">
				<span class="account-save__action">@lang('account.page.save.qr.action')</span>
			</a>
		</div>

		<div class="account-save__option">
			<h3 class="account-save__label"><label for="restore-url">@lang('account.page.save.url.label')</label></h3>
			<div class="account-save__control">
				<input class="account-save__input" type="text" id="restore-url" name="restore-url"
					value="{{ $settingsUrl }}" readonly autocomplete="off" spellcheck="false"
					data-1p-ignore="true" data-lpignore="true" data-form-type="other" data-bwignore>
				<button class="account-save__button" type="button" data-copies="restore-url"
					data-done="@lang('key-create.copy.done')" hidden>@lang('account.page.save.url.action')</button>
			</div>
			<p class="account-save__hint">@lang('account.page.save.url.hint')</p>
		</div>

		@isset($loginCodeUrl)
			<div class="account-save__option" id="account-transfer-way" hidden>
				<h3 class="account-save__label">@lang('account.page.save.transfer.label')</h3>
				{{--
					Ohne Skript gibt es keinen Code — er ist zehn Sekunden gültig
					und müsste sonst bei jedem Seitenaufruf neu geholt werden,
					auch von jemandem, der ihn gar nicht will. Verborgen ist
					deshalb der ganze Weg und nicht nur sein Knopf: als Stapel
					mit Trennlinien wäre ein Block aus Überschrift und einem
					Satz über einen Knopf, den es nicht gibt, ein leeres
					Versprechen. resources/js/account.js deckt ihn auf.

					Der Knopf steht in derselben .account-save__control wie die
					Felder darüber, damit alle vier Wege dieselbe Zeilenkante
					haben — auch der, der kein Feld hat.
				--}}
				<div class="account-save__control">
					<button class="account-save__button" type="button"
						id="account-transfer-open">@lang('account.page.save.transfer.action')</button>
				</div>
				<p class="account-save__hint">@lang('account.page.save.transfer.hint')</p>
			</div>
		@endisset
	</div>
</section>
