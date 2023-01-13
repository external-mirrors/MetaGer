@extends('layouts.subPages')

@section('title', 'Fehler 410 - Resultpage Expired')

@section('content')
	<style>
		main#main-content {
			align-items: center;
			justify-content: center;
		}
	</style>
	<h1>{{ trans('410.title') }}</h1>
	<p>{{ trans('410.text') }}</p>
    <div>
        <a href="{{ $refresh }}" target="_top">Ergebnisseite neu laden</a>
    </div>
@endsection
