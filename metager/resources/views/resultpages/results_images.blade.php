<div id="container" class="image-container">
	@foreach($metager->getResults() as $result)
		@include('layouts.image_result', ['result' => $result])
	@endforeach
</div>
@include('parts.pager')
<div id="external-search">
	<h3>Hier fehlen einige Suchergebnisse</h3>
	<div class="texts">
		<div>Sie haben lediglich die Datenbank von Pixabay durchsucht. Besser suchen Sie mit einem MetaGer Schlüssel.</div>
	</div>
	<div class="external-links">
		<a href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}" class="btn btn-primary">MetaGer Schlüssel kaufen</a>
		<div class="divider">oder</div>
		<form id="external-engines-form" class="external-engines" method="POST">
			@php
			$expiration = now()->addHour(1);
			@endphp
			<input type="hidden" name="expiration" value="{{ $expiration }}">
			<input type="hidden" name="signature" value="{{ hash_hmac('sha256', $expiration, config('app.key')) }}">
			<button type="submit" name="bilder_setting_external" value="google" class="btn btn-default">Suchen auf Google</button>
			<button type="submit" name="bilder_setting_external" value="bing" class="btn btn-default">Suchen auf Bing</button>
		</form>
		<div class="spacer"></div>
		<div class="input-group">
			<input type="checkbox" name="save-external-engine" id="save-external-engine" form="external-engines-form" value="1">
			<label for="save-external-engine">Diese Entscheidung merken </label>
			<button type="button" class="hover" id="save-external-hint">?</button>
			<div class="hint">Wir merken uns Ihre Entscheidung mit Hilfe eines Cookies und leiten Sie bei der nächsten Bildersuche direkt zum ausgewählten Anbieter.</div>
		</div>
	</div>
</div>
