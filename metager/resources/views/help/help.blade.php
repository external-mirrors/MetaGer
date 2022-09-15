@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1>{!! trans('help/help.title') !!}</h1>
<h2>{!! trans('help/help.tableofcontents.1.0') !!}</h2>
<div class="help-topic-row">
	<a id=help-topic-mainpage href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/hauptseiten") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.1') !!}</p>
	</a>
	<a id=help-topic-searchfield href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/hauptseiten#suchfeld") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.2') !!}</p>
	</a>
	<a id=help-topic-result href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/hauptseiten#ergebnis") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.3') !!}</p>
	</a>
	<a id=help-topic-settings href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/hauptseiten#einstellungen") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.1.4') !!}</p>
	</a>
</div>

<h2>{!! trans('help/help.tableofcontents.2.0') !!}</h2>
<div class="help-topic-row">
	<a id=help-topic-searchfunctions href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/funktionen") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.1') !!}</p>
	</a>
	<a id=help-topic-bangs href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/funktionen#bangs") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.2') !!}<br></p>
	</a>
	<a id=help-topic-searchinsearch href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/funktionen#searchinsearch") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.3') !!}<br></p>
	</a>
	<a id=help-topic-addmetager href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/funktionen#selist") }}" class="help-topic">
		<p>{!! trans('help/help.tableofcontents.2.4') !!}<br></p>
	</a>
</div>

<h2>{!! trans('help/help.tableofcontents.3.0') !!}</h2>
	<div class="help-topic-row">
		<a id=help-topic-tracking href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/datensicherheit#tracking") }}" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.2') !!}</p>
		</a>
		<a id=help-topic-tor href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/datensicherheit#torhidden") }}" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.3') !!}<br></p>
		</a>
		<a id= help-topic-proxy href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/datensicherheit#proxy") }}" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.4') !!}<br></p>
		</a>
		<a id=help-topic-content href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/datensicherheit#content") }}" class="help-topic"><p>{!! trans('help/help.tableofcontents.3.5') !!}<br></p>
		</a>
	</div>
		
<h2>{!! trans('help/help.tableofcontents.4.0') !!}</h2>

	<div class="help-topic-row">
		<a id=help-topic-app href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/dienste") }}" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.1') !!}<br></p>
		</a>
		</a>
		<a id=help-topic-asso href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/dienste#asso") }}" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.3') !!}<br></p>
		</a>
		<a id=help-topic-widget href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/dienste#widget") }}" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.4') !!}<br></p>
		</a>		
		<a id=help-topic-maps href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/dienste#map") }}" class="help-topic">
			<p>{!! trans('help/help.tableofcontents.4.5') !!}<br></p>
		</a>
		
	</div>
@endsection