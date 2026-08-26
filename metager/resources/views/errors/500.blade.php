@extends('layouts.subPages')

@section('title', 'Fehler 500 - Interner Serverfehler')

@section('content')
	<div class="error-page">
		<div class="error-page__code">500</div>
		<h1 class="error-page__title">{{ trans('500.title') }}</h1>
		<p class="error-page__text">{{ trans('500.text') }}</p>
		@if( config('app.debug') )
			<details class="error-page__debug" open>
				<summary>Debug</summary>
				<pre>{{ $exception }}</pre>
			</details>
		@endif
	</div>
@endsection
