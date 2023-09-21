@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')

	<h1 class="page-title">{!! trans('help/easy-language/glossary.title') !!}</h1>
	<div id="navigationsticky">
		@if($previous_url !== null)<div><a class="back-button"><img class="back-arrow" src=/img/back-arrow.svg>{{__("results.zurueck")}}</a></div>@endif
		<a class=up-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/easy-language/glossary") }}"><img class="up-arrow" src=/img/up-arrow.svg>{!! trans('help/easy-language/glossary.uparrow') !!}</a>
	</div>
	<div class="card">

	<h2>{!! trans('help/easy-language/glossary.tableofcontents') !!}</h2>
		<ol>
			<li>
			<a href="#glsearchengine" >{!! trans('help/easy-language/glossary.entry.1') !!}</a>
			</li>

			<li>
			<a href="#glstandardsearch" >{!! trans('help/easy-language/glossary.entry.2') !!}</a>
			</li>

			<li>
			<a href="#glsearchcategories" >{!! trans('help/easy-language/glossary.entry.3') !!}</a>
			</li>

			<li>
			<a href="#glfilter" >{!! trans('help/easy-language/glossary.entry.4') !!}</a>
			</li>

			<li>
			<a href="#glurl" >{!! trans('help/easy-language/glossary.entry.5') !!}</a>
			</li>

			<li>
			<a href="#gltab" >{!! trans('help/easy-language/glossary.entry.6') !!}</a>
			</li>

			<li>
			<a href="#glopenanonymously" >{!! trans('help/easy-language/glossary.entry.7') !!}</a>
			</li>

			<li>
			<a href="#glsafesearch" >{!! trans('help/easy-language/glossary.entry.8') !!}</a>
			</li>

			<li>
			<a href="#glbangs" >{!! trans('help/easy-language/glossary.entry.9') !!}</a>
			</li>

			<li>
			<a href="#glcookies" >{!! trans('help/easy-language/glossary.entry.10') !!}</a>
			</li>

			<li>
			<a href="#gldomain" >{!! trans('help/easy-language/glossary.entry.11') !!}</a>
			</li>

			<li>
			<a href="#glbrowser" >{!! trans('help/easy-language/glossary.entry.12') !!}</a>
			</li>

			<li>
			<a href="#gltorhidden" >{!! trans('help/easy-language/glossary.entry.13') !!}</a>
			</li><li>
			<a href="#gltorbrowser" >{!! trans('help/easy-language/glossary.entry.14') !!}</a>
			</li><li>
			<a href="#glapp" >{!! trans('help/easy-language/glossary.entry.15') !!}</a>
			</li><li>
			<a href="#glassoziator" >{!! trans('help/easy-language/glossary.entry.16') !!}</a>
			</li><li>
			<a href="#glwidget" >{!! trans('help/easy-language/glossary.entry.17') !!}</a>
			</li>

		</ol>
	</div>
	<div class="card" id="glsearchengine">
		<h2>{!! trans('help/easy-language/glossary.entry.1') !!}</h2>

		<p>{!! trans('help/easy-language/glossary.explanation.entry1.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry1.2') !!}</p>
		<h3>{!! trans('help/easy-language/glossary.explanation.entry1.3') !!}</h3>
		<p>{!! trans('help/easy-language/glossary.explanation.entry1.4') !!}</p>

	</div>
	<div class="card" id="glstandardsearch">
		<h2>{!! trans('help/easy-language/glossary.entry.2') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry2.1') !!}</p>
	</div>	
	<div class="card" id="glsearchcategories">
		<h2>{!! trans('help/easy-language/glossary.entry.3') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry3.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry3.2') !!}</p>
	</div>
	<div class="card" id="glfilter">
		<h2>{!! trans('help/easy-language/glossary.entry.4') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry4.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry4.2') !!}</p>
	</div>
	<div class="card" id="glurl">
		<h2>{!! trans('help/easy-language/glossary.entry.5') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry5.1') !!}</p>

	</div>
	<div class="card" id="gltab">
		<h2>{!! trans('help/easy-language/glossary.entry.6') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry6.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry6.2') !!}</p>

	</div>
	<div class="card" id="glopenanonymously">
		<h2>{!! trans('help/easy-language/glossary.entry.7') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry7.2') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry7.3') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry7.4') !!}</p>
		<h3>{!! trans('help/easy-language/glossary.explanation.entry7.0') !!}</h3>
		<p>{!! trans('help/easy-language/glossary.explanation.entry7.1') !!}</p>

		
	</div>
	<div class="card" id="glsafesearch">
		<h2>{!! trans('help/easy-language/glossary.entry.8') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry8.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry8.2') !!}</p>

	</div>
	<div class="card" id="glbangs">
		<h2>{!! trans('help/easy-language/glossary.entry.9') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry9.1') !!}</p>


	</div>
	<div class="card" id="glcookies">
		<h2>{!! trans('help/easy-language/glossary.entry.10') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry10.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry10.2') !!}</p>
	</div>
	<div class="card" id="gldomain">
		<h2>{!! trans('help/easy-language/glossary.entry.11') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry11.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry11.2') !!}</p>

	</div>
	<div class="card" id="glbrowser">
		<h2>{!! trans('help/easy-language/glossary.entry.12') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry12.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry12.2') !!}</p>
	</div>
	<div class="card" id="gltorhidden">
		<h2>{!! trans('help/easy-language/glossary.entry.13') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry13.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry13.2') !!}</p>
	</div>
	<div class="card" id="gltorbrowser">
		<h2>{!! trans('help/easy-language/glossary.entry.14') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry14.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry14.2') !!}</p>
	</div>
	<div class="card" id="glapp">
		<h2>{!! trans('help/easy-language/glossary.entry.15') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry15.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry15.2') !!}</p>
	</div>
	<div class="card" id="glassoziator">
		<h2>{!! trans('help/easy-language/glossary.entry.16') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry16.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry16.2') !!}</p>
	</div>
	<div class="card" id="glwidget">
		<h2>{!! trans('help/easy-language/glossary.entry.17') !!}</h2>
		<p>{!! trans('help/easy-language/glossary.explanation.entry17.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry17.2') !!}</p>
	</div>
@endsection