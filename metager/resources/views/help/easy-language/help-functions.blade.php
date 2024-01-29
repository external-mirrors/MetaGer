@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<section class="help-section">
	<h1 class="page-title">{!! trans('help/easy-language/help-functions.title') !!}</h1>
	<div id="navigationsticky">
		<a class="back-button"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/help-functions.backarrow') !!}</a>
	</div>
	<section id="help-searchfunctions" class="help-section card">
	<p>{!! trans('help/easy-language/help-functions.glossary') !!}</p>

		<h2 id="eh-searchfunctions">{!! trans('help/easy-language/help-functions.suchfunktion.title') !!}</h2>
		<h3 id="eh-stopwordsearch">{!! trans('help/easy-language/help-functions.stopworte.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-functions.stopworte.1') !!}</p>
			<p>{!! trans('help/easy-language/help-functions.stopworte.2') !!}</p>

			<ul class="dotlist">
				<li class="nodot"><div class="search-example">{!! trans('help/easy-language/help-functions.stopworte.3') !!}</div></li>
			</ul>
			<p>{!! trans('help/easy-language/help-functions.stopworte.4') !!}</p>

		</div>
		<h3 id="eh-severalwords">{!! trans('help/easy-language/help-functions.mehrwortsuche.title') !!}</h3>
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
		{{--
		<h3 id="eh-exactsearch">{!! trans('help/easy-language/help-functions.exactsearch.title') !!}</h3>
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
		--}}
	</section>
	<section id="eh-bangs" class="help-section card">
		<h3>{!! trans('help/easy-language/help-functions.bang.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-functions.bang.1') !!}</p>
			<p class = "search-example">{!! trans('help/easy-language/help-functions.bang.example') !!}</p>
			<p>{!! trans('help/easy-language/help-functions.bang.2') !!}</p>
			@if (App\Localization::getLanguage() == "de")
			<img id="help-easy-language-bang-image" class="help-easy-language-image lm-only" src="/img/help/help-bangs-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help/help-bangs-dm.png"/>
			@else
			<img id="help-easy-language-bang-image" class="help-easy-language-image lm-only" src="/img/help/help-bangs-lm-en.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help/help-bangs-dm-en.png"/>
			@endif
			<p>{!! trans('help/easy-language/help-functions.bang.3') !!}</p>

		</div>
	</section>
	<section id="eh-keyexplain" class="help-section card">
		<h3>{!! trans('help/easy-language/help-functions.key.maintitle') !!}</h3>
		<div>
			<h4>{!! trans('help/easy-language/help-functions.key.title.1') !!}</h4>
			<p>{!! trans('help/easy-language/help-functions.key.1') !!}</p>
			<ul>
				<li>{!! trans('help/easy-language/help-functions.key.2.1') !!}</li>
				@if (App\Localization::getLanguage() == "de")
				<img class="help-easy-language-image lm-only" src="/img/help/help-key-login-button-lm.png"/>
				<img class="help-easy-language-image dm-only" src="/img/help/help-key-login-button-dm.png"/> 
				@else
				<img class="help-easy-language-image lm-only" src="/img/help/help-key-login-button-lm-en.png"/>
				<img class="help-easy-language-image dm-only" src="/img/help/help-key-login-button-dm-en.png"/> 
				@endif
				<p>{!! trans('help/easy-language/help-functions.key.2.2') !!}</p>
				@if (App\Localization::getLanguage() == "de")
				<img class="help-easy-language-image lm-only" src="/imghelp//help-key-login-code-lm.png"/>
				<img class="help-easy-language-image dm-only" src="/img/help/help-key-login-code-dm.png"/>
				@else
				<img class="help-easy-language-image lm-only" src="/img/help/help-key-login-code-lm-en.png"/>
				<img class="help-easy-language-image dm-only" src="/img/help/help-key-login-code-dm-en.png"/>
				@endif
				<p>{!! trans('help/easy-language/help-functions.key.2.3') !!}</p>
				<br>
				<li>{!! trans('help/easy-language/help-functions.key.3.1') !!}</li>
				@if (App\Localization::getLanguage() == "de")
				<img class="help-easy-language-image lm-only" src="/img/help/help-key-url-button-lm.png"/>
				<img class="help-easy-language-image dm-only" src="/img/help/help-key-url-button-dm.png"/>
				@else
				<img class="help-easy-language-image lm-only" src="/img/help/help-key-url-button-lm-en.png"/>
				<img class="help-easy-language-image dm-only" src="/img/help/help-key-url-button-dm-en.png"/>
				@endif
				<p>{!! trans('help/easy-language/help-functions.key.3.2') !!}</p>
				<li>{!! trans('help/easy-language/help-functions.key.4.1') !!}</li>
				@if (App\Localization::getLanguage() == "de")
				<img class="help-easy-language-image help-easy-language-key-image lm-only" src="/img/help/help-key-add-lm.png"/> 
				<img class="help-easy-language-image help-easy-language-key-image dm-only" src="/img/help/help-key-add-dm.png"/> 
				@else
				<img class="help-easy-language-image help-easy-language-key-image lm-only" src="/img/help/help-key-add-lm-en.png"/> 
				<img class="help-easy-language-image help-easy-language-key-image dm-only" src="/img/help/help-key-add-dm-en.png"/> 
				@endif
				<p>{!! trans('help/easy-language/help-functions.key.4.2') !!}</p>
				@if (App\Localization::getLanguage() == "de")
				<img class="help-easy-language-image help-easy-language-key-image lm-only" src="/img/help/help-key-add-file-lm.png"/>
				<img class="help-easy-language-image help-easy-language-key-image dm-only" src="/img/help/help-key-add-file-dm.png"/>
				@else
				<img class="help-easy-language-image help-easy-language-key-image lm-only" src="/img/help/help-key-add-file-lm-en.png"/>
				<img class="help-easy-language-image help-easy-language-key-image dm-only" src="/img/help/help-key-add-file-dm-en.png"/>
				@endif
				<p>{!! trans('help/easy-language/help-functions.key.4.3') !!}</p>
				<li>{!! trans('help/easy-language/help-functions.key.5.1') !!}</li>
				@if (App\Localization::getLanguage() == "de")
				<img class="help-easy-language-image help-easy-language-key-image lm-only" src="/img/help/help-key-add-lm.png"/>
				<img class="help-easy-language-image help-easy-language-key-image dm-only" src="/img/help/help-key-add-dm.png"/>
				@else
				<img class="help-easy-language-image help-easy-language-key-image lm-only" src="/img/help/help-key-add-lm-en.png"/>
				<img class="help-easy-language-image help-easy-language-key-image dm-only" src="/img/help/help-key-add-dm-en.png"/>
				@endif
				<p>{!! trans('help/easy-language/help-functions.key.5.2') !!}</p>
				@if (App\Localization::getLanguage() == "de")
				<img class="help-easy-language-image help-easy-language-key-image lm-only" src="/img/help/help-key-qr-code-lm.png"/>
				<img class="help-easy-language-image help-easy-language-key-image dm-only" src="/img/help/help-key-qr-code-dm.png"/>
				@else
				<img class="help-easy-language-image help-easy-language-key-image lm-only" src="/img/help/help-key-qr-code-lm-en.png"/>
				<img class="help-easy-language-image help-easy-language-key-image dm-only" src="/img/help/help-key-qr-code-dm-en.png"/>
				@endif
				<p>{!! trans('help/easy-language/help-functions.key.5.3') !!}</p>
				<li>{!! trans('help/easy-language/help-functions.key.6') !!}</li>
			</ul>
			<h4>{!! trans('help/easy-language/help-functions.key.title.1') !!}</h4>
			<p>{!! trans('help/easy-language/help-functions.key.7') !!}</p>
			<img src="/img/key-icon.svg" alt="{!! trans('help/easy-language/help-functions.key.alt.none') !!}" aria-hidden="true" id="searchbar-img-key">
			<p>{!! trans('help/easy-language/help-functions.key.8') !!}</p>
			<img src="/img/key-empty.svg" alt="{!! trans('help/easy-language/help-functions.key.alt.empty') !!}" aria-hidden="true" id="searchbar-img-key">
			<p>{!! trans('help/easy-language/help-functions.key.9') !!}</p>
			<img src="/img/key-full.svg" alt="{!! trans('help/easy-language/help-functions.key.alt.full') !!}" aria-hidden="true" id="searchbar-img-key">
			<p>{!! trans('help/easy-language/help-functions.key.10') !!}</p>
			<img src="/img/key-low.svg" alt="{!! trans('help/easy-language/help-functions.key.alt.low') !!}" aria-hidden="true" id="searchbar-img-key">
			<p>{!! trans('help/easy-language/help-functions.key.11') !!}</p>
		</div>
	</section>
	<section id="eh-selist" class="help-section card">
		<h3>{!! trans('help/easy-language/help-functions.selist.title.0') !!}</h3>
		<h4>{!! trans('help/easy-language/help-functions.selist.title.1') !!}</h4>
		<p>{!! trans('help/easy-language/help-functions.selist.explanation.1') !!}</p>
		@if (App\Localization::getLanguage() == "de")
		<img id="help-easy-language-install-metager-image" class="help-easy-language-image lm-only" src="/img/help/help-install-metager-lm.png"/>
		<img class="help-easy-language-image dm-only" src="/img/help/help-install-metager-dm.png"/>
		@else
		<img id="help-easy-language-install-metager-image" class="help-easy-language-image lm-only" src="/img/help/help-install-metager-lm-en.png"/>
		<img class="help-easy-language-image dm-only" src="/img/help/help-install-metager-dm-en.png"/>
		@endif
		<p>{!! trans('help/easy-language/help-functions.selist.explanation.2') !!}</p>
	</section>
</section>
@endsection