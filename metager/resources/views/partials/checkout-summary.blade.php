{{--
	Die Kurzfassung des gewählten Pakets, die über jedem Schritt des
	Bezahlvorgangs steht: Menge, Preis, und ein Weg zurück zur Paketwahl.

	$price ist der Euro-Betrag des Pakets (App\Landing\KeyPrice::tiers()). Auf
	/konto/aufladen/<menge> stand er von Anfang an; auf den Zahlweisen-Seiten
	fehlte er, bis App\Http\Controllers\ChargeController::render() ihn mitgab —
	Feedback dazu: wer eine Zahlweise wählt, will dabei sehen, was das Paket
	kostet, nicht nur wie viele Tokens es sind.

	Der null-Zweig ist nur Absicherung: jeder Aufrufer von render() hat $amount
	schon gegen knownTier() geprüft, und der Preis kommt aus derselben Liste —
	steht die Menge dort, steht auch ihr Preis. Zeigt lieber nur die Tokenzahl
	als ein nacktes „ €", falls das je auseinanderläuft.
--}}
<section class="checkout-summary">
	<span class="checkout-summary__amount">@lang('account.page.charge.tokens', ['amount' => \Illuminate\Support\Number::format($amount, locale: app()->getLocale())])</span>
	@if(!is_null($price))
		<span class="checkout-summary__price">@lang('account.page.charge.price', ['price' => $price])</span>
	@endif
	<a class="checkout-summary__change" href="{{ $changeAmountUrl }}">@lang('checkout.page.change')</a>
</section>
