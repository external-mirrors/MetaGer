@extends('layouts.subPages')

@section('title', 'Fehler 403 - Unautorisiert')

@section('content')
	<div class="error-page">
		<div class="error-page__code">403</div>
		<h1 class="error-page__title">Unautorisiert</h1>
		<p class="error-page__text">Sie haben leider keine Rechte auf dieses Dokument zuzugreifen.</p>
	</div>
@endsection
