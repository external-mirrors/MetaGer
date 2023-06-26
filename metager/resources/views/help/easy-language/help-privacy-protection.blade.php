@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<section class="help-section">
<h1 class="page-title">{!! trans('help/easy-language/help-privacy-protection.title') !!}</h1>
<div id="navigationsticky">
	<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/easy-language") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/help-privacy-protection.backarrow') !!}</a>
</div>
	<p>{!! trans('help/easy-language/help-privacy-protection.glossary') !!}</p>
	<h2>{!! trans('help/easy-language/help-privacy-protection.datenschutz.title') !!}</h2>
	<section id="tracking">
		<h3>{!! trans('help/easy-language/help-privacy-protection.datenschutz.1') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-privacy-protection.datenschutz.2') !!}</p>
			<p>{!! trans('help/easy-language/help-privacy-protection.datenschutz.3') !!}</p>
		</div>
	</section>
	<section id="torhidden">
		<h3>{!! trans('help/easy-language/help-privacy-protection.tor.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-privacy-protection.tor.1') !!}</p>
			<p>{!! trans('help/easy-language/help-privacy-protection.tor.2') !!}</p>
		</div>
	</section>
	<section id="proxy">
		<h3>{!! trans('help/easy-language/help-privacy-protection.proxy.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-privacy-protection.proxy.1') !!}</p>
				<img class="help-easy-language-image lm-only" src="/img/help-anonym-lm.png"/>
				<img class="help-easy-language-image dm-only" src="/img/help-anonym-dm.png"/>
			<p>{!! trans('help/easy-language/help-privacy-protection.proxy.2') !!}</p>


		</div>
	</section>

	<section id="content">
		<h3>{!! trans('help/easy-language/help-privacy-protection.content.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-privacy-protection.content.explanation.1') !!}</p>
			<p>{!! trans('help/easy-language/help-privacy-protection.content.explanation.2') !!}</p>
		</div>
	</section>
</section>
    @endsection