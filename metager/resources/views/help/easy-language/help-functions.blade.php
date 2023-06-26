@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<section class="help-section">
	<h1 class="page-title">{!! trans('help/easy-language/help-functions.title') !!}</h1>
	<div id="navigationsticky">
		<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/easy-language") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/help-functions.backarrow') !!}</a>
	</div>
	<p>{!! trans('help/easy-language/help-functions.glossary') !!}</p>

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
	<section id="bangs">
		<h3>{!! trans('help/easy-language/help-functions.bang.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-functions.bang.1') !!}</p>
			<p class = "search-example">{!! trans('help/easy-language/help-functions.bang.example') !!}</p>
			<p>{!! trans('help/easy-language/help-functions.bang.2') !!}</p>
			<img class="help-easy-language-image lm-only" src="/img/help-bangs-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-bangs-dm.png"/>
			<p>{!! trans('help/easy-language/help-functions.bang.3') !!}</p>

		</div>
	</section>
	<section id="keyexplain">
		<h3>{!! trans('help/easy-language/help-functions.key.maintitle') !!}</h3>
		<div>
			<h4>{!! trans('help/easy-language/help-functions.key.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-functions.key.1') !!}</p>
			<ul>
				<li>{!! trans('help/easy-language/help-functions.key.2') !!}</li>
				<li>{!! trans('help/easy-language/help-functions.key.3') !!}</li>
				<li>{!! trans('help/easy-language/help-functions.key.4') !!}</li>
				<li>{!! trans('help/easy-language/help-functions.key.5') !!}</li>
				<li>{!! trans('help/easy-language/help-functions.key.6') !!}</li>
			</ul>
			<p>{!! trans('help/easy-language/help-functions.key.7') !!}</p>
			<p>{!! trans('help/easy-language/help-functions.key.8') !!}</p>
			<p>{!! trans('help/easy-language/help-functions.key.9') !!}</p>

		</div>
	</section>
	<section id="selist">
		<h3>{!! trans('help/easy-language/help-functions.selist.title.0') !!}</h3>
		<h4>{!! trans('help/easy-language/help-functions.selist.title.1') !!}</h4>
		<p>{!! trans('help/easy-language/help-functions.selist.explanation.1') !!}</p>
		<img class="help-easy-language-image lm-only" src="/img/help-install-metager-lm.png"/>
		<img class="help-easy-language-image dm-only" src="/img/help-install-metager-dm.png"/>
		<p>{!! trans('help/easy-language/help-functions.selist.explanation.2') !!}</p>
	</section>
</section>
@endsection