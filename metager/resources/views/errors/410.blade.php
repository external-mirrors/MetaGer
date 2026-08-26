@extends('layouts.subPages')

@section('title', 'Fehler 410 - Resultpage Expired')

@section('content')
	<div class="error-page">
		<div class="error-page__code">410</div>
		<h1 class="error-page__title">{{ trans('410.title') }}</h1>
		<p class="error-page__text">{{ trans('410.text') }}</p>
		<div class="error-page__actions">
			<a href="{{ $refresh }}" target="_top" class="btn btn-primary">Ergebnisseite neu laden</a>
		</div>
	</div>
@endsection
