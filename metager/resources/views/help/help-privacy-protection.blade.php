@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('help/help-privacy-protection.title') !!}</h1>
<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/help-privacy-protection.backarrow') !!}</a>
<h2>{!! trans('help/help-privacy-protection.datenschutz.title') !!}</h2>
	<section id="tracking">
		<h3>{!! trans('help/help-privacy-protection.datenschutz.1') !!}</h3>
		<div>
			<p>{!! trans('help/help-privacy-protection.datenschutz.2') !!}</p>
			<p>{!! trans('help/help-privacy-protection.datenschutz.3') !!}</p>
		</div>
	</section>
	<section id="torhidden">
		<h3>{!! trans('help/help-privacy-protection.tor.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-privacy-protection.tor.1') !!}</p>
			<p>{!! trans('help/help-privacy-protection.tor.2') !!}</p>
		</div>
	</section>
	<section id="proxy">
		<h3>{!! trans('help/help-privacy-protection.proxy.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-privacy-protection.proxy.1') !!}</p>
		</div>
	</section>

	<section id="content">
		<h3>{!! trans('help/help-privacy-protection.content.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-privacy-protection.content.explanation.1') !!}</p>
			<p>{!! trans('help/help-privacy-protection.content.explanation.2') !!}</p>
		</div>
	</section>
    @endsection