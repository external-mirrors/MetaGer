@extends('layouts.subPages')

@section('title', 'Fehler 404 - Seite nicht gefunden')

@section('content')
	<style>
		main#main-content {
			align-items: center;
			justify-content: center;
		}
	</style>
	<h1>{{ trans('404.title') }}</h1>
	<p>{{ trans('404.text') }}</p>
@endsection
