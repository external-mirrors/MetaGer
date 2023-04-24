@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<section class="help-section">
	<h1 class="page-title">{!! trans('help/easy-language/help-functions.title') !!}</h1>
	<div id="navigationsticky">
		<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/easy-language") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/help-functions.backarrow') !!}</a>
	</div>
		<h2 id="searchfunctions">{!! trans('help/easy-language/help-functions.suchfunktion.title') !!}</h2>
		<h3 id="stopwordsearch">{!! trans('help/easy-language/help-functions.stopworte.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-functions.stopworte.1') !!}</p>
			<p>{!! trans('help/easy-language/help-functions.stopworte.2') !!}</p>

			<ul class="dotlist">
				<li class="nodot"><div class="search-example">{!! trans('help/easy-language/help-functions.stopworte.3') !!}</div></li>
			</ul>
			<p>{!! trans('help/easy-language/help-functions.stopworte.4') !!}</p>

		</div>
		<h3 id="severalwords">{!! trans('help/easy-language/help-functions.mehrwortsuche.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-functions.mehrwortsuche.1') !!}</p>
			<p>{!! trans('help/easy-language/help-functions.mehrwortsuche.2') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/easy-language/help-functions.mehrwortsuche.3.0') !!}</li>
				<li class="nodot"><div class = "search-example">{!! trans('help/easy-language/help-functions.mehrwortsuche.3.example') !!}</div></li>
			</ul>
			<p>{!! trans('help/easy-language/help-functions.mehrwortsuche.4') !!}</p>
			<ul class="dotlist">
			<li>{!! trans('help/easy-language/help-functions.mehrwortsuche.5.0') !!}</li>
				<li class="nodot"><div class = "search-example">{!! trans('help/easy-language/help-functions.mehrwortsuche.5.example') !!}</div></li>
			</ul>
		</div>
		<h3 id="exactsearch">{!! trans('help/easy-language/help-functions.exactsearch.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-functions.exactsearch.1') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/easy-language/help-functions.exactsearch.2') !!}</li>
				<li class="nodot"><div class = "search-example">{!! trans('help/easy-language/help-functions.exactsearch.example.1') !!}</div></li>
				<p>{!! trans('help/easy-language/help-functions.exactsearch.3') !!}</p>
				<li>{!! trans('help/easy-language/help-functions.exactsearch.4') !!}</li>
				<li class="nodot"><div class = "search-example">{!! trans('help/easy-language/help-functions.exactsearch.example.2') !!}</div></li>
			</ul>
		</div>
</section>
	<section id="bangs">
		<h3>{!! trans('help/easy-language/help-functions.bang.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-functions.bang.1') !!}</p>
			<p class = "search-example">{!! trans('help/easy-language/help-functions.bang.example') !!}</p>
			<p>{!! trans('help/easy-language/help-functions.bang.2') !!}</p>
			<img class="help-easy-language-mainpages-image" src="/img/help-bangs-easy-language.png"/>
			<p>{!! trans('help/easy-language/help-functions.bang.3') !!}</p>

		</div>
	</section>
	<section id="searchinsearch">
		<h3>{!! trans('help/easy-language/help-functions.searchinsearch.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-functions.searchinsearch.1') !!}</p>
			<div class="image-container"><img src="/img/help-php-resultpic-01-easy-lang.png"/></div>
			<p>{!! trans('help/easy-language/help-functions.searchinsearch.2') !!}</p>
			<div class="image-container"><img src="/img/help-php-resultpic-02-easy-lang.png"/></div>
			<p>{!! trans('help/easy-language/help-functions.searchinsearch.3') !!}</p>
			<div class ="image-container"><img src="/img/help-easy-language-search-in-search.png"/></div>
			<p>{!! trans('help/easy-language/help-functions.searchinsearch.4') !!}</p>

		</div>
	</section>
	<section id="selist">
		<h3>{!! trans('help/easy-language/help-functions.selist.title.0') !!}</h3>
		<h4>{!! trans('help/easy-language/help-functions.selist.title.1') !!}</h4>
		<p>{!! trans('help/easy-language/help-functions.selist.explanation.1') !!}</p>
		<div class="image-container"><img src="/img/help-settings-install-metager.jpg"/></div>
		<p>{!! trans('help/easy-language/help-functions.selist.explanation.2') !!}</p>
	</section>
@endsection