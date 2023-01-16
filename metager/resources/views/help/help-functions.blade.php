@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('help/help-functions.title') !!}</h1>
<section>
<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/help-functions.backarrow') !!}</a>
		<h2 id="searchfunctions">{!! trans('help/help-functions.suchfunktion.title') !!}</h2>
		<h3 id="stopwordsearch">{!! trans('help/help-functions.stopworte.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-functions.stopworte.1') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/help-functions.stopworte.2') !!}</li>
				<li class="nodot"><div class="search-example">{!! trans('help/help-functions.stopworte.3') !!}</div></li>
			</ul>
		</div>
		<h3 id="urls">{!! trans('help/help-functions.urls.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-functions.urls.explanation') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/help-functions.urls.example_a') !!}</li>
				<li class="nodot"><div class = "search-example">{!! trans('help/help-functions.urls.example_b') !!}</div></li>
			</ul>
		</div>
		<h3 id="severalwords">{!! trans('help/help-functions.mehrwortsuche.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-functions.mehrwortsuche.1') !!}</p>
			<p>{!! trans('help/help-functions.mehrwortsuche.2') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/help-functions.mehrwortsuche.3.text') !!}</li>
				<li class="nodot"><div class = "search-example">{!! trans('help/help-functions.mehrwortsuche.3.example') !!}</div></li>
				<li>{!! trans('help/help-functions.mehrwortsuche.4.text') !!}</li>
				<li class="nodot"><div class = "search-example">{!! trans('help/help-functions.mehrwortsuche.4.example') !!}</div></li>
			</ul>
		</div>

	</section>
	<section id="bangs">
		<h3>{!! trans('help/help-functions.bang.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-functions.bang.1') !!}</p>
		</div>
	</section>
	<section id="searchinsearch">
		<h3>{!! trans('help/help-functions.searchinsearch.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-functions.searchinsearch.1') !!}</p>
		</div>
	</section>
	<section id="selist">
		<h3>{!! trans('help/help-functions.selist.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-functions.selist.explanation_a') !!}</p>
			<p>{!! trans('help/help-functions.selist.explanation_b') !!}</p>

		</div>
	</section>

@endsection