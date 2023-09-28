@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<section class="help-section">
	<h1 class="page-title">{!! trans('help/easy-language/help-services.title') !!}</h1>
	<div id="navigationsticky">
		<a class="back-button"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/help-services.backarrow') !!}</a>
	</div>
	<section class="help-section card">
		<p>{!! trans('help/easy-language/help-services.glossary') !!}</p>
		<h2>{!! trans('help/easy-language/help-services.dienste.1') !!}</h2>
	</section>
	<section id="help-app" class="help-section card">
		<div id="eh-mg-app" style="margin-top: -100px"></div>
		<div style="margin-top: 100px"></div>
		<h3>{!! trans('help/easy-language/help-services.app.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-services.app.1') !!}</p>
		</div>
	</section>
	<section id="eh-asso" class="help-section card">
		<h3>{!! trans('help/easy-language/help-services.suchwortassoziator.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-services.suchwortassoziator.1') !!}</p>
			<p>{!! trans('help/easy-language/help-services.suchwortassoziator.2') !!}</p>
		</div>
	</section>
	<section id="eh-widget" class="help-section card">
		<h3>{!! trans('help/easy-language/help-services.widget.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-services.widget.1') !!}</p>
		</div>
	</section>
	<section id="eh-maps" class="help-section card">
	<h3>{!! trans('help/easy-language/help-services.maps.title') !!}</h3>
	<div>
		<p>{!! trans('help/easy-language/help-services.maps.1') !!}</p>
		@if (App\Localization::getLanguage() == "de")
		<img id="easy-help-services-maps" class="help-easy-language-image lm-only" src="/img/help-maps-01-lm.png"/>
		<img id="easy-help-services-maps"class="help-easy-language-image dm-only" src="/img/help-maps-01-dm.png"/>
		@else
		@endif
		<p>{!! trans('help/easy-language/help-services.maps.2') !!}</p>
		<img id="easy-help-services-maps-right-list" src="/img/help-easy-lang-maps-right-list.png"/>
	</div>
	</section>
</section>
@endsection