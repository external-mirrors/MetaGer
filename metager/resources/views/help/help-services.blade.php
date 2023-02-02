@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('help/help-services.title') !!}</h1>
<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/help-services.backarrow') !!}</a>
<h2 id="dienste">{!! trans('help/help-services.dienste.text') !!}</h2>
	<h3><img class= "mg-icon" src="/img/angle-double-right.svg" alt="{{ trans('angle-double-right.alt') }}" aria-hidden= "true"> {!! trans('help/help-services.dienste.kostenlos') !!}</h3>
	<section id="app">
		<div id="mg-app" style="margin-top: -100px"></div>
		<div style="margin-top: 100px"></div>
		<h3>{!! trans('help/help-services.app.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-services.app.1') !!}</p>
		</div>
	</section>

	<section id="asso">
		<h3>{!! trans('help/help-services.suchwortassoziator.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-services.suchwortassoziator.1') !!}</p>
			<p>{!! trans('help/help-services.suchwortassoziator.2') !!}</p>
			<p>{!! trans('help/help-services.suchwortassoziator.3') !!}</p>
		</div>
	</section>
	<section id="widget">
		<h3>{!! trans('help/help-services.widget.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-services.widget.1') !!}</p>
		</div>
	</section>
	<section id="maps">
		<h3>{!! trans('help/help-services.maps.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-services.maps.1') !!}</p>
			<p>{!! trans('help/help-services.maps.2') !!}</p>
			<p>{!! trans('help/help-services.maps.3') !!}</p>
		</div>
@endsection