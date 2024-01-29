@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<section class="help-section">
	<h1 class="page-title">{!! trans('help/easy-language/help-privacy-protection.title') !!}</h1>
	<div id="navigationsticky">
		<a class="back-button"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/help-privacy-protection.backarrow') !!}</a>
	</div>
	<section id="eh-tracking" class="help-section card">
		<p>{!! trans('help/easy-language/help-privacy-protection.glossary') !!}</p>
		<h2>{!! trans('help/easy-language/help-privacy-protection.datenschutz.title') !!}</h2>
		<h3>{!! trans('help/easy-language/help-privacy-protection.datenschutz.1') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-privacy-protection.datenschutz.2') !!}</p>
			<p>{!! trans('help/easy-language/help-privacy-protection.datenschutz.3') !!}</p>
		</div>
	</section>
	<section id="eh-torhidden" class="help-section card">
		<h3>{!! trans('help/easy-language/help-privacy-protection.tor.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-privacy-protection.tor.1') !!}</p>
			<p>{!! trans('help/easy-language/help-privacy-protection.tor.2') !!}</p>
		</div>
	</section>
	<section id="eh-proxy" class="help-section card">
		<h3>{!! trans('help/easy-language/help-privacy-protection.proxy.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-privacy-protection.proxy.1') !!}</p>
			@if (App\Localization::getLanguage() == "de")
			<img class="help-easy-language-image lm-only" src="/img/help/help-anonym-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help/help-anonym-dm.png"/>
			@else
			<img class="help-easy-language-image lm-only" src="/img/help/help-anonym-lm-en.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help/help-anonym-dm-en.png"/>
			@endif
			<p>{!! trans('help/easy-language/help-privacy-protection.proxy.2') !!}</p>
		</div>
	</section>
	<section id="eh-content" class="help-section card">
		<h3>{!! trans('help/easy-language/help-privacy-protection.content.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-privacy-protection.content.explanation.1') !!}</p>
			<p>{!! trans('help/easy-language/help-privacy-protection.content.explanation.2') !!}</p>
		</div>
	</section>
</section>
@endsection