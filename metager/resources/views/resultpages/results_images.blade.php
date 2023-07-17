<div id="container" class="image-container">
	@foreach($metager->getResults() as $result)
		@include('layouts.image_result', ['result' => $result])
	@endforeach
</div>
@include('parts.pager')
<div id="external-search">
	<h3>Hier fehlen einige Suchergebnisse</h3>
	<div class="texts">
		<div>Sie haben lediglich die Datenbank von Pixabay durchsucht. Besser suchen Sie mit einem <a href="#">MetaGer Schlüssel</a>.</div>
	</div>
	<div class="external-links">
		<a href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}" class="btn btn-default">MetaGer Schlüssel kaufen</a>
		<div class="divider">oder</div>
		<div class="external-engines">
			<a href="#" class="btn btn-default">Suchen auf Google</a>
			<a href="#" class="btn btn-default">Suchen auf Bing</a>
			<div class="input-group">
				<input type="checkbox" name="save-external-engine" id="save-external-engine">
				<label for="save-external-engine">Diese Entscheidung merken </label>
				<button class="hover" id="save-external-hint">?</button>
				<div class="hint">Wir merken uns Ihre Entscheidung mit Hilfe eines Cookies und leiten Sie bei der nächsten Bildersuche direkt zum ausgewählten Anbieter.</div>
			</div>
		</div>
	</div>
</div>
