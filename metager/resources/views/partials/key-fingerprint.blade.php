{{--
	Welcher Schlüssel gerade angemeldet ist — Kürzel plus Identicon, nie der
	Schlüssel selbst. Aus account.blade.php herausgezogen, damit die
	Aufladeseiten (resources/views/checkout/*.blade.php) dieselbe Kennung
	zeigen können, ohne eine zweite Fassung dieser drei Zeilen zu pflegen. Wer
	Geld gegen einen Schlüssel einzahlt, soll dieselbe Gewissheit haben wie im
	Konto: welcher Schlüssel das ist.

	Erwartet $fingerprint.
--}}
<p class="account-head__id">
	{!! \App\Authentication\KeyIdenticon::render($fingerprint) !!}
	<span class="account-head__fingerprint">
		@if($fingerprint !== null)
			@lang('account.page.fingerprint', ['fingerprint' => strtoupper($fingerprint)])
		@else
			@lang('account.page.fingerprint_unknown')
		@endif
	</span>
</p>
