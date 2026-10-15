{{--
	Die Zustimmung zur Ausführung vor Ablauf der Widerrufsfrist, bevor es zu
	suma-payments geht (checkout/suma-payments.blade.php).

	Der Wortlaut ist rechtlich vorgegeben und bleibt unverändert. Was sich
	geändert hat, ist die Darstellung: er stand in voller Länge **fett** — nicht durch eine Entscheidung, sondern weil
	general/forms.less jedem `label` `font-weight: bold` gibt. Vier bis sechs
	Zeilen durchgehend fetter Kleintext direkt über dem einzigen Knopf der
	Seite lesen sich als Hindernis, nicht als Bedingung, und was durchgehend
	fett ist, hebt nichts hervor.

	Jetzt: normal gesetzt, in einem eigenen getönten Feld, mit einem
	Kontrollkästchen, das man auch mit dem Daumen trifft (.checkout-consent
	in pages/checkout.less). Die AGB-Zeile steht mit im Feld statt darüber —
	beides ist dieselbe Sache, nämlich das, dem man zustimmt.

--}}
<div class="checkout-consent">
	{{-- Enthält eigene <a>, deshalb roh ausgegeben statt mit @lang. --}}
	<p class="checkout-consent__agb">{!! __('checkout.consent.agb', ['agblink' => route('agb')]) !!}</p>

	<div class="checkout-consent__box">
		<input type="checkbox" name="revocation" id="checkout-revocation" required>
		<label for="checkout-revocation">{!! __('checkout.consent.label', [
			'revocation_link' => route('agb'),
			'refundlink' => route('agb') . '#rueckerstattung',
		]) !!}</label>
	</div>
</div>
