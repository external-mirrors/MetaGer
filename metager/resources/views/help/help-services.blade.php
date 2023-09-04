@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('help/help-services.title') !!}</h1>
<section>
	<div id="navigationsticky">
		<a class="back-button"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/help-services.backarrow') !!}</a>
	</div>
	<h2 id="h-dienste">{!! trans('help/help-services.dienste.text') !!}</h2>
	<section id="app" class="card">
			<h3>{!! trans('help/help-services.app.title') !!}</h3>
			<p>{!! trans('help/help-services.app.1') !!}</p>
	</section>
	<section id="h-asso" class="card">
		<h3>{!! trans('help/help-services.suchwortassoziator.title') !!}</h3>
		<p>{!! trans('help/help-services.suchwortassoziator.1') !!}</p>
		<p>{!! trans('help/help-services.suchwortassoziator.2') !!}</p>
		<p>{!! trans('help/help-services.suchwortassoziator.3') !!}</p>
	</section>
	<section id="h-widget" class="card">
		<h3>{!! trans('help/help-services.widget.title') !!}</h3>
		<p>{!! trans('help/help-services.widget.1') !!}</p>
	</section>
	<section id="h-maps" class="card">
		<h3>{!! trans('help/help-services.maps.title') !!}</h3>
		<p>{!! trans('help/help-services.maps.1') !!}</p>
		<p>{!! trans('help/help-services.maps.2') !!}</p>
		<p>{!! trans('help/help-services.maps.3') !!}</p>
	</section>
</section>
@endsection