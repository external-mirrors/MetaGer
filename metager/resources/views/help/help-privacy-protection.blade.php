@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('help/help-privacy-protection.title') !!}</h1>
<section>
	<div id="navigationsticky">
		<a class="back-button"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/help-privacy-protection.backarrow') !!}</a>
	</div>
	<p>{!! trans('help/help-privacy-protection.easy-help') !!}</p>
	<h2>{!! trans('help/help-privacy-protection.privacy.title') !!}</h2>
	<section id="h-tracking" class="card">
		<h3>{!! trans('help/help-privacy-protection.privacy.1') !!}</h3>
		<p>{!! trans('help/help-privacy-protection.privacy.2') !!}</p>
		<p>{!! trans('help/help-privacy-protection.privacy.3') !!}</p>
	</section>
	<section id="h-torhidden" class="card">
		<h3>{!! trans('help/help-privacy-protection.tor.title') !!}</h3>
		<p>{!! trans('help/help-privacy-protection.tor.1') !!}</p>
		<p>{!! trans('help/help-privacy-protection.tor.2') !!}</p>
	</section>
	<section id="h-proxy" class="card">
		<h3>{!! trans('help/help-privacy-protection.proxy.title') !!}</h3>
		<p>{!! trans('help/help-privacy-protection.proxy.1') !!}</p>
	</section>
	<section id="h-content" class="card">
		<h3>{!! trans('help/help-privacy-protection.content.title') !!}</h3>
		<p>{!! trans('help/help-privacy-protection.content.explanation.1') !!}</p>
		<p>{!! trans('help/help-privacy-protection.content.explanation.2') !!}</p>
	</section>
</section>
@endsection