@extends('layouts.subPages')

@section('title', 'Fehler 404 - Seite nicht gefunden')

@section('content')
	<div class="error-page">
		<div class="error-page__code">404</div>
		<h1 class="error-page__title">{{ trans('404.title') }}</h1>
		<p class="error-page__text">{{ trans('404.text') }}</p>
	</div>
@endsection
