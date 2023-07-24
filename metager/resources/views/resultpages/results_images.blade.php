<div id="container" class="image-container">
	@foreach($metager->getResults() as $result)
		@include('layouts.image_result', ['result' => $result])
	@endforeach
</div>
@include('parts.pager')
@if(!app(\App\Models\Authorization\Authorization::class)->canDoAuthenticatedSearch())
<div id="external-search">
	<h3>@lang("results.images.external.heading")</h3>
	<div class="texts">
		<div>@lang("results.images.external.description")</div>
	</div>
	<div class="external-links">
		<a href="{{ app(\App\Models\Authorization\Authorization::class)->getAdfreeLink() }}" class="btn btn-primary">@lang("results.images.external.buy")</a>
		<div class="divider">@lang("results.images.external.or")</div>
		<form id="external-engines-form" class="external-engines" method="POST">
			@php
			$expiration = now()->addHour(1);
			@endphp
			<input type="hidden" name="expiration" value="{{ $expiration }}">
			<input type="hidden" name="signature" value="{{ hash_hmac('sha256', $expiration, config('app.key')) }}">
			<button type="submit" name="bilder_setting_external" value="google" class="btn btn-default">@lang("results.images.external.google")</button>
			<button type="submit" name="bilder_setting_external" value="bing" class="btn btn-default">@lang("results.images.external.bing")</button>
		</form>
		<div class="spacer"></div>
		<div class="input-group">
			<input type="checkbox" name="save-external-engine" id="save-external-engine" form="external-engines-form" value="1">
			<label for="save-external-engine">@lang("results.images.external.save")</label>
		</div>
	</div>
</div>
@endif
