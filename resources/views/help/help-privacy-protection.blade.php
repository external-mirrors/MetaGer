@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h2>{!! trans('help/help-privacy-protection.datenschutz.title') !!}</h2>
	<section id="factcheck">
		<h3>{!! trans('help/help-privacy-protection.datenschutz.faktencheck.heading') !!}</h3>
		<div>
			<p>@lang('help/help-privacy-protection.datenschutz.faktencheck.body.1')</p>
			<p>@lang('help/help-privacy-protection.datenschutz.faktencheck.body.2')</p>
		</div>
	</section>
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