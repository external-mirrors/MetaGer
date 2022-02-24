@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
	<h1 class="page-title">{!! trans('help/easy-language/help-mainpages.title') !!}</h1>

	<section id="startpage" class="help-section">
		<a  class=help-back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe/easy-language") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/easy-language/help-mainpages.backarrow') !!}</a>
		<h2>{!! trans('help/easy-language/help-mainpages.title.2') !!}</h2>

		<h3 id="startseite">{!! trans('help/easy-language/help-mainpages.startpage.title') !!}</h3>
		<p>{!! trans('help/easy-language/help-mainpages.startpage.info') !!}</p>
		<img id="help-vertial-menu-image" src="/img/help-vertical-menu.png"/>
		<p>{!! trans('help/easy-language/help-mainpages.startpage.info.1') !!}</p>
		<p>{!! trans('help/easy-language/help-mainpages.startpage.info.2') !!}</p>
		<p>{!! trans('help/easy-language/help-mainpages.startpage.info.3') !!}</p>


		<h3 id="suchfeld">{!! trans('help/easy-language/help-mainpages.searchfield.title') !!}</h3>
		<div>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.info') !!}</p>
			<h4>{!! trans('help/easy-language/help-mainpages.searchfield.memberkey') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.memberkey.1') !!}</p>
			<img class="help-easy-language-mainpages-image" src="/img/help-left-searchfield.jpg"/>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.memberkey.2') !!}</p>

			<h4>{!! trans('help/easy-language/help-mainpages.searchfield.slot') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.slot.1') !!}</p>
			<img class="help-easy-language-mainpages-image" src="/img/help-middle-searchfield.jpg"/>
			<h4>{!! trans('help/easy-language/help-mainpages.searchfield.search') !!}</h4>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.search.1') !!}<p>
			<img class="help-easy-language-mainpages-image" src="/img/help-right-searchfield.jpg"/>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.search.2') !!}</p>
			<p>{!! trans('help/easy-language/help-mainpages.searchfield.morefunctions') !!}</p>
		</div>
        <h3 id="ergebnis">{!! trans('help/easy-language/help-mainpages.resultpage.title') !!}</h3>
		    <div>
				<p>{!! trans('help/easy-language/help-mainpages.resultpage.foci') !!}</p>
				<img class="help-easy-language-mainpages-image" src="/img/help-search-focus.jpg"/>
				<p>{!! trans('help/easy-language/help-mainpages.resultpage.foci.1') !!}</p>

				<p>{!! trans('help/easy-language/help-mainpages.resultpage.choice') !!}</p>
				<img class="help-easy-language-mainpages-image" src="/img/help-settings-and-filter.jpg"/>
				<p>{!! trans('help/easy-language/help-mainpages.resultpage.filter') !!}</p>
				<p id="difset">{!! trans('help/easy-language/help-mainpages.resultpage.settings') !!}</p>
            </div>
		<h3>{!! trans('help/easy-language/help-mainpages.result.title') !!}</h3>
			<div>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.1') !!}</p>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.open') !!}</p>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.newtab') !!}</p>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.anonym') !!}</p>
				<p>{!! trans('help/easy-language/help-mainpages.result.info.more') !!}</p>

				<p>{!! trans('help/easy-language/help-mainpages.result.info.2') !!}</p>
					<p>{!! trans('help/easy-language/help-mainpages.result.info.saveresult') !!}</p>
					<p>{!! trans('help/easy-language/help-mainpages.result.info.domainnewsearch') !!}</p>
					<p>{!! trans('help/easy-language/help-mainpages.result.info.hideresult') !!}</p>
			</div>
        <h3 id="einstellungen">{!! trans('help/easy-language/help-mainpages.settings.title') !!}</h3>
                <p>@lang('help/easy-language/help-mainpages.settings.1', ["link" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('showAllSettings', ['url' => url()->full()])) ])</li>
                <p>{!! trans('help/easy-language/help-mainpages.settings.2') !!}</p>
                <p>{!! trans('help/easy-language/help-mainpages.settings.3') !!}</p>
                <p>{!! trans('help/easy-language/help-mainpages.settings.4') !!}</p>
                <p>{!! trans('help/easy-language/help-mainpages.settings.5') !!}</p>
                <p>{!! trans('help/easy-language/help-mainpages.settings.6') !!}</p>
                <p>{!! trans('help/easy-language/help-mainpages.settings.7') !!}</p>
    </section>



@endsection