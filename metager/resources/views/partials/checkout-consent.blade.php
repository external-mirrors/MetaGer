{{--
	Die Zustimmung zur Ausführung vor Ablauf der Widerrufsfrist, geteilt von
	Bar, micropayment, Wero und PayPal.

	Der Wortlaut ist rechtlich vorgegeben und bleibt unverändert. Was sich
	geändert hat, ist die Darstellung: er stand auf allen vier Seiten in
	voller Länge **fett** — nicht durch eine Entscheidung, sondern weil
	general/forms.less jedem `label` `font-weight: bold` gibt. Vier bis sechs
	Zeilen durchgehend fetter Kleintext direkt über dem einzigen Knopf der
	Seite lesen sich als Hindernis, nicht als Bedingung, und was durchgehend
	fett ist, hebt nichts hervor.

	Jetzt: normal gesetzt, in einem eigenen getönten Feld, mit einem
	Kontrollkästchen, das man auch mit dem Daumen trifft (.checkout-consent
	in pages/checkout.less). Die AGB-Zeile steht mit im Feld statt darüber —
	beides ist dieselbe Sache, nämlich das, dem man zustimmt.

	`$boxId`/`$boxHidden` sind für PayPal: dort deckt resources/js/checkout-
	paypal.js das Kästchen erst auf, wenn das SDK die Zahlweise als
	verfügbar gemeldet hat (`onInit`), und braucht dafür eine Kennung am
	Kästchen selbst. Jede andere Seite lässt beides weg.
--}}
<div class="checkout-consent">
	{{-- Enthält eigene <a>, deshalb roh ausgegeben statt mit @lang. --}}
	<p class="checkout-consent__agb">{!! __('checkout.consent.agb', ['agblink' => route('agb')]) !!}</p>

	<div class="checkout-consent__box" @isset($boxId) id="{{ $boxId }}" @endisset @if($boxHidden ?? false) hidden @endif>
		<input type="checkbox" name="revocation" id="checkout-revocation" required>
		<label for="checkout-revocation">{!! __('checkout.consent.label', [
			'revocation_link' => route('agb'),
			'refundlink' => route('agb') . '#rueckerstattung',
		]) !!}</label>
	</div>
</div>
