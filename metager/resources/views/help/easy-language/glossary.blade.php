@extends('layouts.subPages')

@section('title', $title )

@section('content')

	<h1 class="page-title">{!! trans('help/easy-language/glossary.title') !!}</h1>
	<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/glossary.backarrow') !!}</a>
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
	<div class="card" id="standardsuche">
	<h2>{!! trans('help/easy-language/glossary.entry.2') !!}</h2>
	
	<h3>{!! trans('help/easy-language/glossary.explanation.entry2.1') !!}</h3>
	<p>{!! trans('help/easy-language/glossary.explanation.entry2.2') !!}
	</div>	
@endsection