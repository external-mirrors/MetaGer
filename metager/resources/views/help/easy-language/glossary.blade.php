@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')

	<h1 class="page-title">{!! trans('help/easy-language/glossary.title') !!}</h1>
	<div id="navigationsticky">
		@if($previous_url !== null)<div><a  class=back-button href="{{$previous_url}}"><img class="back-arrow" src=/img/back-arrow.svg>{{__("results.zurueck")}}</a></div>@endif
		<a class=up-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/easy-language/glossary") }}"><img class="up-arrow" src=/img/up-arrow.svg>{!! trans('help/easy-language/glossary.uparrow') !!}</a>
	</div>
	<div class="card">

	<h2>{!! trans('help/easy-language/glossary.tableofcontents') !!}</h2>
		<ol>
			<li>
			<a href="#installieren" >{!! trans('help/easy-language/glossary.entry.1') !!}</a>
			</li>

			<li>
			<a href="#standardsuche" >{!! trans('help/easy-language/glossary.entry.2') !!}</a>
			</li>

			<li>
			<a href="#suchkategorien" >{!! trans('help/easy-language/glossary.entry.3') !!}</a>
			</li>

			<li>
			<a href="#filter" >{!! trans('help/easy-language/glossary.entry.4') !!}</a>
			</li>

			<li>
			<a href="#url" >{!! trans('help/easy-language/glossary.entry.5') !!}</a>
			</li>

			<li>
			<a href="#tab" >{!! trans('help/easy-language/glossary.entry.6') !!}</a>
			</li>

			<li>
			<a href="#anonymoeffnen" >{!! trans('help/easy-language/glossary.entry.7') !!}</a>
			</li>

			<li>
			<a href="#safesearch" >{!! trans('help/easy-language/glossary.entry.8') !!}</a>
			</li>

			<li>
			<a href="#bangs" >{!! trans('help/easy-language/glossary.entry.9') !!}</a>
			</li>

			<li>
			<a href="#cookies" >{!! trans('help/easy-language/glossary.entry.10') !!}</a>
			</li>

			<li>
			<a href="#domain" >{!! trans('help/easy-language/glossary.entry.11') !!}</a>
			</li>

			<li>
			<a href="#browser" >{!! trans('help/easy-language/glossary.entry.12') !!}</a>
			</li>

		</ol>
	</div>
	<div class="card" id="suchmaschine">
		<h2>{!! trans('help/easy-language/glossary.entry.1') !!}</h2>

		<p>{!! trans('help/easy-language/glossary.explanation.entry1.1') !!}</p>
		<p>{!! trans('help/easy-language/glossary.explanation.entry1.2') !!}</p>
		<h3>{!! trans('help/easy-language/glossary.explanation.entry1.3') !!}</h3>
		<p>{!! trans('help/easy-language/glossary.explanation.entry1.4') !!}</p>

	</div>
	<div class="card" id="standardsuche">
		<h2>{!! trans('help/easy-language/glossary.entry.2') !!}</h2>
			<p>{!! trans('help/easy-language/glossary.explanation.entry2.1') !!}
	</div>	
	<div class="card" id="suchkategorien">
		<h2>{!! trans('help/easy-language/glossary.entry.3') !!}</h2>
	</div>
	<div class="card" id="filter">
		<h2>{!! trans('help/easy-language/glossary.entry.4') !!}</h2>
	</div>
	<div class="card" id="url">
		<h2>{!! trans('help/easy-language/glossary.entry.5') !!}</h2>
	</div>
	<div class="card" id="tab">
		<h2>{!! trans('help/easy-language/glossary.entry.6') !!}</h2>
	</div>
	<div class="card" id="anonymoeffnen">
		<h2>{!! trans('help/easy-language/glossary.entry.7') !!}</h2>
	</div>
	<div class="card" id="safesearch">
		<h2>{!! trans('help/easy-language/glossary.entry.8') !!}</h2>
	</div>
	<div class="card" id="bangs">
		<h2>{!! trans('help/easy-language/glossary.entry.9') !!}</h2>
	</div>
	<div class="card" id="cookies">
		<h2>{!! trans('help/easy-language/glossary.entry.10') !!}</h2>
	</div>
	<div class="card" id="domain">
		<h2>{!! trans('help/easy-language/glossary.entry.11') !!}</h2>
	</div>
	<div class="card" id="browser">
		<h2>{!! trans('help/easy-language/glossary.entry.12') !!}</h2>
	</div>
@endsection