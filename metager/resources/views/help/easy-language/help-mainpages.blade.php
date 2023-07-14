@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
<section class="help-section">
	<h1 class="page-title">{!! trans('help/easy-language/help-mainpages.title') !!}</h1>
	<div id="navigationsticky">
		<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/easy-language") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/help-mainpages.backarrow') !!}</a>
	</div>
	<p>{!! trans('help/easy-language/help-mainpages.glossary') !!}</p>

	<h2>{!! trans('help/easy-language/help-mainpages.title.2') !!}</h2>

	<section id="startpage" class="help-section card">
		
		<h3 id="startseite">{!! trans('help/easy-language/help-mainpages.startpage.title') !!}</h3>
		<p>{!! trans('help/easy-language/help-mainpages.startpage.info.1') !!}</p>
		<img id="help-vertial-menu-image" src="/img/menu.svg"/>
		<p>{!! trans('help/easy-language/help-mainpages.startpage.info.2') !!}</p>
		<p>{!! trans('help/easy-language/help-mainpages.startpage.info.3') !!}</p>
		<p>{!! trans('help/easy-language/help-mainpages.startpage.info.4') !!}</p>


		<h3 id="suchfeld">{!! trans('help/easy-language/help-mainpages.searchfield.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.info') !!}</p>
			<h4>{!! trans('help/easy-language/help-mainpages.searchfield.memberkey.1') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.memberkey.2') !!}</p>
			<img class="help-easy-language-image lm-only" src="/img/help-left-searchfield-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-left-searchfield-dm.png"/>

			<p>{!! trans('help/easy-language/help-mainpages.searchfield.memberkey.3') !!}</p>

			<h4>{!! trans('help/easy-language/help-mainpages.searchfield.slot.1') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.slot.2') !!}</p>
			<img class="help-easy-language-image lm-only" src="/img/help-middle-searchfield-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-middle-searchfield-dm.png"/>
			<h4>{!! trans('help/easy-language/help-mainpages.searchfield.search.1') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.search.2') !!}<p>
			<img class="help-easy-language-image lm-only" src="/img/help-right-searchfield-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-right-searchfield-dm.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.search.3') !!}</p>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.morefunctions') !!}</p>
		</div>
        <h3 id="ergebnis">{!! trans('help/easy-language/help-mainpages.resultpage.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-mainpages.resultpage.foci.1') !!}</p>
			<img class="help-easy-language-image lm-only" src="/img/help-search-focus-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-search-focus-dm.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.resultpage.foci.2') !!}</p>

			<p>{!! trans('help/easy-language/help-mainpages.resultpage.choice') !!}</p>
			<img class="help-easy-language-image lm-only" src="/img/help-settings-filter-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-settings-filter-dm.png"/>
			<h4>{!! trans('help/easy-language/help-mainpages.resultpage.filter.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.resultpage.filter.1') !!}</p>

			<h4>{!! trans('help/easy-language/help-mainpages.resultpage.settings.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.resultpage.settings.1') !!}</p>

		</div>
		<h3>{!! trans('help/easy-language/help-mainpages.result.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-mainpages.result.info.1') !!}</p>
			<div class="image-container">
				<img class="lm-only" src="/img/help-php-resultpic-01.png" alt="Bildschirmfoto eines Suchergebnisses"/>
				<img class="dm-only" src="/img/help-php-resultpic-01-dm.png" alt="Bildschirmfoto eines Suchergebnisses"/>
			</div>
			<h4>{!! trans('help/easy-language/help-mainpages.result.info.open.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.result.info.open.0') !!}</p>
			<ul>
				<li>{!! trans('help/easy-language/help-mainpages.result.info.open.1') !!}</li>
				<li>{!! trans('help/easy-language/help-mainpages.result.info.open.2') !!}</li>
				<li>{!! trans('help/easy-language/help-mainpages.result.info.open.3') !!}</li>
			</ul>				
			<h4>{!! trans('help/easy-language/help-mainpages.result.info.newtab.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.result.info.newtab.1') !!}</p>
			<h4>{!! trans('help/easy-language/help-mainpages.result.info.anonym.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.result.info.anonym.1') !!}</p>
			<h4>{!! trans('help/easy-language/help-mainpages.result.info.more.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.result.info.more.1') !!}</p>
			<div class="image-container">
				<img class="lm-only" src="/img/help-php-resultpic-02.png" alt="Bildschirmfoto eines Suchergebnisses"/>
				<img class="dm-only" src="/img/help-php-resultpic-02-dm.png" alt="Bildschirmfoto eines Suchergebnisses"/>
			</div>
			<p>{!! trans('help/easy-language/help-mainpages.result.info.2') !!}</p>
				<h4>{!! trans('help/easy-language/help-mainpages.result.info.domainnewsearch.title') !!}</h4>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.domainnewsearch.1') !!}</p>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.domainnewsearch.2') !!}</p>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.domainnewsearch.3') !!}</p>
				<h4>{!! trans('help/easy-language/help-mainpages.result.info.hideresult.title') !!}</h4>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.hideresult.1') !!}</p>
		</div>
        <h3 id="einstellungen">{!! trans('help/easy-language/help-mainpages.settings.title') !!}</h3>
			<h4>{!! trans('help/easy-language/help-mainpages.settings.metagerkey.title') !!}</h4>
			<img class="help-easy-language-image lm-only" src="/img/help-settings-key-lm-01.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-settings-key-dm-01.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.settings.metagerkey.1') !!}</p>
			<img class="help-easy-language-image lm-only" src="/img/help-settings-key-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-settings-key-dm.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.settings.metagerkey.2') !!}</p>

			<h4>{!! trans('help/easy-language/help-mainpages.settings.searchengine.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.settings.searchengine.1') !!}</p>
			<img id="help-easy-language-used-search-engines-image" class="help-easy-language-image lm-only" src="/img/help-settings-search-engines-lm.png"/>
			<img id="help-easy-language-used-search-engines-image" class="help-easy-language-image dm-only" src="/img/help-settings-search-engines-dm.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.settings.searchengine.2') !!}</p>

			<h4>{!! trans('help/easy-language/help-mainpages.settings.filter.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.settings.filter.1.0') !!}</p>
			<img id="help-easy-language-searchfilter-image" class="help-easy-language-image lm-only" src="/img/help-settings-search-filter-lm.png"/>
			<img id="help-easy-language-searchfilter-image" class="help-easy-language-image dm-only" src="/img/help-settings-search-filter-dm.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.settings.filter.1.1') !!}</p>
			<p>{!! trans('help/easy-language/help-mainpages.settings.filter.1.2') !!}</p>
			<p>{!! trans('help/easy-language/help-mainpages.settings.filter.1.3') !!}</p>
			<p>{!! trans('help/easy-language/help-mainpages.settings.filter.1.4') !!}</p>
			<p>{!! trans('help/easy-language/help-mainpages.settings.filter.2') !!}</p>
			<img id="help-easy-language-safesearch-image" class="help-easy-language-image lm-only" src="/img/help-settings-safesearch-lm.png"/>
			<img id="help-easy-language-safesearch-image" class="help-easy-language-image dm-only" src="/img/help-settings-safesearch-dm.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.settings.filter.3') !!}</p>

			<h4>{!! trans('help/easy-language/help-mainpages.settings.blacklist.title') !!}</h4>
			<img class="help-easy-language-image lm-only" id="easy-help-mainpage-blacklist-image" src="/img/help-settings-blacklist-lm.png"/>
			<img class="help-easy-language-image dm-only" id="easy-help-mainpage-blacklist-image" src="/img/help-settings-blacklist-dm.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.settings.blacklist.1') !!}</p>
			<p>{!! trans('help/easy-language/help-mainpages.settings.blacklist.2') !!}</p>
			<h4>{!! trans('help/easy-language/help-mainpages.settings.moresettings') !!}</h4>
			<img id="help-easy-language-more-settings" class="help-easy-language-image lm-only" src="/img/help-more-settings-lm.png"/>
			<img id="help-easy-language-more-settings" class="help-easy-language-image dm-only" src="/img/help-more-settings-dm.png"/>
			<h4>{!! trans('help/easy-language/help-mainpages.settings.darkmode.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.settings.darkmode.1') !!}</p>
			<h4>{!! trans('help/easy-language/help-mainpages.settings.newtab.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.settings.newtab.1') !!}</p>
			<h4>{!! trans('help/easy-language/help-mainpages.settings.cite.title') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.settings.cite.1') !!}</p>
			<img id="easy-help-mainpages-settings-cite" class="help-easy-language-image lm-only" src="/img/help-settings-cite-lm.png"/>
			<img id="easy-help-mainpages-settings-cite" class="help-easy-language-image dm-only" src="/img/help-settings-cite-dm.png"/>
			<h4>{!! trans('help/easy-language/help-mainpages.settings.cookies.title') !!}</h4>
			
			<img class="help-easy-language-image lm-only" src="/img/help-settings-recover-lm.png"/>
			<img class="help-easy-language-image dm-only" src="/img/help-settings-recover-dm.png"/>
			<p>{!! trans('help/easy-language/help-mainpages.settings.cookies.1') !!}</p>	
    </section>
</section>


@endsection