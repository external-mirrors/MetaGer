@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<h1 class="page-title">{!! trans('help/help-functions.title') !!}</h1>
<section>
	<div id="navigationsticky">
		<a class="back-button"><img class="back-arrow" src=/img/svg-icons/back-arrow.svg>{!! trans('help/help-functions.backarrow') !!}</a>
	</div>
	<p>{!! trans('help/help-functions.easy-help') !!}</p>
	<section id="h-searchfunctions" class="card">
		<h2>{!! trans('help/help-functions.searchfunction.title') !!}</h2>
		<h3 id="h-stopwordsearch">{!! trans('help/help-functions.stopwords.title') !!}</h3>
		<p>{!! trans('help/help-functions.stopwords.1') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('help/help-functions.stopwords.2') !!}</li>
			<li class="nodot"><div class="search-example">{!! trans('help/help-functions.stopwords.3') !!}</div></li>
		</ul>
		<h3 id="h-urls">{!! trans('help/help-functions.urls.title') !!}</h3>
		<p>{!! trans('help/help-functions.urls.explanation') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('help/help-functions.urls.example_a') !!}</li>
			<li class="nodot"><div class = "search-example">{!! trans('help/help-functions.urls.example_b') !!}</div></li>
		</ul>
		<h3 id="h-severalwords">{!! trans('help/help-functions.multiwordsearch.title') !!}</h3>
		<p>{!! trans('help/help-functions.multiwordsearch.1') !!}</p>
		<p>{!! trans('help/help-functions.multiwordsearch.2') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('help/help-functions.multiwordsearch.3.text') !!}</li>
			<li class="nodot"><div class = "search-example">{!! trans('help/help-functions.multiwordsearch.3.example') !!}</div></li>
			<li>{!! trans('help/help-functions.multiwordsearch.4.text') !!}</li>
			<li class="nodot"><div class = "search-example">{!! trans('help/help-functions.multiwordsearch.4.example') !!}</div></li>
		</ul>
		{{--
		<h3 id="h-exactsearch">{!! trans('help/help-functions.exactsearch.title') !!}</h3>
		<p>{!! trans('help/help-functions.exactsearch.1') !!}</p>
		<ul class="dotlist">
			<li>{!! trans('help/help-functions.exactsearch.2') !!}</li>
			<li class="nodot"><div class = "search-example">{!! trans('help/help-functions.exactsearch.example.1') !!}</div></li>
			<li>{!! trans('help/help-functions.exactsearch.3') !!}</li>
			<li class="nodot"><div class = "search-example">{!! trans('help/help-functions.exactsearch.example.2') !!}</div></li>
		</ul>
		--}}
	</section>
	<section id="h-bangs" class="card">
		<h3>{!! trans('help/help-functions.bang.title') !!}</h3>
		<p>{!! trans('help/help-functions.bang.1') !!}</p>
		<h4>{!! trans('help/help-functions.bang.2') !!}</h4>	
		<p>{!! trans('help/help-functions.bang.3') !!}</p>	
	</section>
	{{-- Der Anker bleibt: der Hilfe-Index und mehrere Sprachdateien verlinken
	     ihn. Der Inhalt ist auf einen Einstieg zusammengeschrumpft — die vier
	     Einrichtungswege standen wortgleich auch in der FAQ des Keymanagers,
	     die jetzt /hilfe/schluessel ist, und zwei Stellen, die dasselbe
	     erklären, driften auseinander. Der Abschnitt „Farbiger MetaGer
	     Schlüssel“ ist ganz weg: er beschrieb das Schlüsselsymbol in der
	     Suchleiste, das die Kontopille ersetzt hat. --}}
	<section id="h-keyexplain" class="card">
		<h3>{!! trans('help/help-functions.key.title') !!}</h3>
		<p>{!! trans('help/help-functions.key.1') !!}</p>
		<p><a href="{{ route('key-faq') }}">{{ trans('help/help-functions.key.more') }}</a></p>
	</section>
	<section id="h-selist" class="card">
		<h3>{!! trans('help/help-functions.selist.title') !!}</h3>
		<p>{!! trans('help/help-functions.selist.explanation_a') !!}</p>
		<p>{!! trans('help/help-functions.selist.explanation_b') !!}</p>
	</section>
</section>



@endsection