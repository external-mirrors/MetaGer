{{--
	Die Kurzfassung des gewählten Pakets, die über jedem Schritt des
	Bezahlvorgangs steht: was gekauft wird, was es kostet, was danach auf dem
	Schlüssel liegt, und ein Weg zurück zur Paketwahl.

	$price ist der Euro-Betrag des Pakets (App\Landing\KeyPrice::tiers()). Auf
	/konto/aufladen/<menge> stand er von Anfang an; auf den Zahlweisen-Seiten
	fehlte er, bis App\Http\Controllers\ChargeController::render() ihn mitgab —
	Feedback dazu: wer eine Zahlweise wählt, will dabei sehen, was das Paket
	kostet, nicht nur wie viele Tokens es sind.

	**Beschriftete Felder statt dreier nackter Zahlen nebeneinander.** Vorher
	stand hier „1.000 Token   10 €   Menge ändern“ in einer Zeile, ohne dass
	irgendetwas sagte, welche Zahl was ist. Jetzt trägt jede Zahl ihr Label,
	und daneben steht die, wegen der der ganze Vorgang stattfindet: das
	Guthaben *danach*. Der Preis allein beantwortet die nicht, sobald schon
	etwas auf dem Schlüssel liegt.

	$currentCharge ist das heutige Guthaben oder null (Keyserver gerade nicht
	erreichbar). Bei null fällt nur die „danach“-Spalte weg — der Rest der
	Kurzfassung steht dann trotzdem.

	Der null-Zweig bei $price ist Absicherung: jeder Aufrufer von render() hat
	$amount schon gegen knownTier() geprüft, und der Preis kommt aus derselben
	Liste — steht die Menge dort, steht auch ihr Preis. Zeigt lieber nur die
	Tokenzahl als ein nacktes „ €“, falls das je auseinanderläuft.
--}}
<section class="checkout-summary">
	<dl class="checkout-summary__facts">
		<div class="checkout-summary__fact">
			<dt>@lang('checkout.page.summary.buying')</dt>
			<dd class="checkout-summary__amount">@lang('account.page.charge.tokens', ['amount' => \Illuminate\Support\Number::format($amount, locale: app()->getLocale())])</dd>
		</div>
		@if(!is_null($price))
			<div class="checkout-summary__fact">
				<dt>@lang('checkout.page.summary.price')</dt>
				<dd class="checkout-summary__price">@lang('account.page.charge.price', ['price' => $price])</dd>
			</div>
		@endif
		@if(!is_null($currentCharge))
			<div class="checkout-summary__fact checkout-summary__fact--after">
				<dt>@lang('checkout.page.summary.after')</dt>
				<dd class="checkout-summary__after">@lang('account.page.charge.tokens', ['amount' => \Illuminate\Support\Number::format(floor($currentCharge) + $amount, locale: app()->getLocale())])</dd>
			</div>
		@endif
	</dl>
	<a class="checkout-summary__change" href="{{ $changeAmountUrl }}">@lang('checkout.page.change')</a>
</section>
