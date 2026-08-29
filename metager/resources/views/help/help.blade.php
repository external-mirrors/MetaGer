@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1>{!! trans('help/help.title') !!}</h1>
<a id=help-easy-language-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/easy-language") }}"><img class="easy-help-icon easy-help-icon-button lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon easy-help-icon-button dm-only" src="/img/help-questionmark-icon-dm.svg"/>{!! trans('help/help.easy.language') !!} </a>
<h2>{!! trans('help/help.tableofcontents.1.0') !!}</h2>
<div class="help-topic-row">
	<a id=help-topic-mainpage href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/hauptseiten#h-startpage") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.1') !!}</p>
	</a>
	<a id=help-topic-searchfield href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/hauptseiten#h-searchfield") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.2') !!}</p>
	</a>
	<a id=help-topic-result href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/hauptseiten#h-resultpage") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.3') !!}</p>
	</a>
	<a id=help-topic-settings href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/hauptseiten#h-settings") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.4') !!}</p>
	</a>
</div>

<h2>{!! trans('help/help.tableofcontents.2.0') !!}</h2>
<div class="help-topic-row">
	<a id=help-topic-searchfunctions href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/funktionen") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.1') !!}</p>
	</a>
	<a id=help-topic-bangs href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/funktionen#h-bangs") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.2') !!}<br></p>
	</a>
	<a id=help-topic-key href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/funktionen#h-keyexplain") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.3') !!}<br></p>
	</a>
	<a id=help-topic-addmetager href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/funktionen#h-selist") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.4') !!}<br></p>
	</a>
</div>

<h2>{!! trans('help/help.tableofcontents.3.0') !!}</h2>
	<div class="help-topic-row">
		<a id=help-topic-tracking href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/datensicherheit#h-tracking") }}" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.2') !!}</p>
		</a>
		<a id=help-topic-tor href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/datensicherheit#h-torhidden") }}" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.3') !!}<br></p>
		</a>
		<a id= help-topic-proxy href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/datensicherheit#h-proxy") }}" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.4') !!}<br></p>
		</a>
		<a id=help-topic-content href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/datensicherheit#h-content") }}" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.5') !!}<br></p>
		</a>
	</div>
		
<h2>{!! trans('help/help.tableofcontents.4.0') !!}</h2>

	<div class="help-topic-row">
		<a id=help-topic-app href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/dienste") }}" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.1') !!}<br></p>
		</a>
		</a>
		<a id=help-topic-widget href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/dienste#h-widget") }}" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.4') !!}<br></p>
		</a>		
		<a id=help-topic-maps href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/dienste#h-maps") }}" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.5') !!}<br></p>
		</a>
		
	</div>

{{-- Der fünfte Themenblock kam mit dem Umzug von /keys dazu. Die drei Karten
     sind die drei Seiten, die der Keymanager zum Schlüssel hatte: seine FAQ,
     die Token-Erklärung und die Preisseite. --}}
<h2>{!! trans('help/help.tableofcontents.5.0') !!}</h2>
<div class="help-topic-row">
	<a id=help-topic-keyfaq href="{{ route('key-faq') }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.5.1') !!}</p>
	</a>
	<a id=help-topic-anonymous-token href="{{ route('anonymous-token') }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.5.2') !!}</p>
	</a>
	<a id=help-topic-price href="{{ route('price') }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.5.3') !!}</p>
	</a>
</div>
@endsection
