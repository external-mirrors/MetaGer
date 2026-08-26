@extends('layouts.subPages')

@section('title', 'Fehler 503 - Service nicht verfügbar')

{{--
	No-JS fallback for a Redis outage: the browser is told outright to reload
	itself. app/../bootstrap/app.php maps such outages to this exact status
	code with a short Retry-After, on the premise that they are normally over
	within seconds — a reload shortly after is far more useful here than a
	dead-end error page. JS is free to layer a nicer countdown on top, but per
	the progressive-enhancement rule (see CLAUDE.md /
	tests/Browser/ProgressiveEnhancementTest) this tag is what has to work on
	its own with client JS disabled.
--}}
@section('meta_refresh')
	<meta http-equiv="refresh" content="5" />
@endsection

@section('content')
	<div class="error-page">
		<div class="error-page__code">503</div>
		<h1 class="error-page__title">{{ trans('503.title') }}</h1>
		<p class="error-page__text">{{ trans('503.text') }}</p>
		@if( config('app.debug') )
			<details class="error-page__debug" open>
				<summary>Debug</summary>
				<pre>{{ $exception }}</pre>
			</details>
		@endif
	</div>
@endsection
