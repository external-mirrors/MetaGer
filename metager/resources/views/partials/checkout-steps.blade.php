{{--
	Die Schrittleiste des Bezahlvorgangs: Menge → Zahlungsart → Bezahlen.

	Vorher sagte keine Seite dieses Vorgangs, wo im Ablauf man steht. Die
	Paketwahl heißt „Guthaben aufladen“ (/konto#charge), die Zahlweisenwahl
	hieß genauso, und die Seite danach trug nur noch den Namen der Zahlweise —
	drei Seiten, die einzeln aussahen wie der Anfang von etwas.

	`$step` ist 1, 2 oder 3. **Jeder erledigte Schritt ist ein Link**, nicht
	nur der erste: beide liegen gleichermaßen hinter einem, und es gibt für
	beide eine Adresse, die genau dorthin zurückführt. Dass nur „Menge“ eine
	war und „Zahlungsart“ daneben toter Text, war keine Entscheidung, sondern
	die Kachel, an der der erste Entwurf aufgehört hat. Der aktuelle und die
	kommenden Schritte bleiben Text — in einem Bezahlvorgang springt man nicht
	vorwärts.

	<ol> und nicht <div>: die Reihenfolge ist die Aussage. `aria-current="step"`
	trägt sie auch ohne die Farben; die abgeschlossenen Schritte tragen ein
	Häkchen als Text (nicht als Bild), damit sie vorgelesen als „erledigt“
	ankommen und nicht bloß grau sind.
--}}
<nav class="checkout-steps" aria-label="@lang('checkout.page.steps.aria')">
	<ol class="checkout-steps__list">
		@foreach ([1 => ['amount', $changeAmountUrl], 2 => ['method', $methodsUrl]] + [3 => ['pay', null]] as $index => [$name, $backUrl])
			<li class="checkout-steps__step @if($index < $step) checkout-steps__step--done @elseif($index === $step) checkout-steps__step--current @endif"
				@if($index === $step) aria-current="step" @endif>
				@if($index < $step && $backUrl !== null)
					<a class="checkout-steps__link" href="{{ $backUrl }}">
						<span class="checkout-steps__marker" aria-hidden="true">✓</span>
						<span class="checkout-steps__label">@lang('checkout.page.steps.' . $name)</span>
					</a>
				@else
					<span class="checkout-steps__marker" aria-hidden="true">@if($index < $step)✓@else{{ $index }}@endif</span>
					<span class="checkout-steps__label">@lang('checkout.page.steps.' . $name)</span>
				@endif
			</li>
		@endforeach
	</ol>
</nav>
