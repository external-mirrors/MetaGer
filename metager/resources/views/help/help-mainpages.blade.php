@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
	<h1 class="page-title">{!! trans('help/help-mainpages.title.1') !!}</h1>

	<section id="startpage">
		<a  class=back-button href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/hilfe") }}"><img class="back-arrow" src=/img/back-arrow.svg>{!! trans('help/help-mainpages.backarrow') !!}</a>
		<h2>{!! trans('help/help-mainpages.title.2') !!}</h2>

		<h3 id="startseite">{!! trans('help/help-mainpages.startpage.title') !!}</h3>
		<p>{!! trans('help/help-mainpages.startpage.info') !!}</p>
		<h3 id="suchfeld">{!! trans('help/help-mainpages.searchfield.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-mainpages.searchfield.info') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/help-mainpages.searchfield.memberkey') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.slot') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.search') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.morefunctions') !!}</li>
			</ul>
		</div>
        <h3 id="ergebnis">{!! trans('help/help-mainpages.resultpage.title') !!}</h3>
		    <div>
			    <ul class="dotlist">
				    <li>{!! trans('help/help-mainpages.resultpage.foci') !!}</li>
				    <li>{!! trans('help/help-mainpages.resultpage.choice') !!}</li>
				        <ul class="dotlist">
				        	<li>{!! trans('help/help-mainpages.resultpage.filter') !!}</li>
				        	<li id="difset">{!! trans('help/help-mainpages.resultpage.settings') !!}</li>
            </div>
		<h3>{!! trans('help/help-mainpages.result.title') !!}</h3>
			<div>
				<p>{!! trans('help/help-mainpages.result.info.1') !!}</p>
				<ul class = "dotlist">
					<li>{!! trans('help/help-mainpages.result.info.open') !!}</li>
					<li>{!! trans('help/help-mainpages.result.info.newtab') !!}</li>
					<li>{!! trans('help/help-mainpages.result.info.anonym') !!}</li>
					<li>{!! trans('help/help-mainpages.result.info.more') !!}</li>
				</ul>
				<p>{!! trans('help/help-mainpages.result.info.2') !!}</p>
				<ul class = "dotlist">
					<li>{!! trans('help/help-mainpages.result.info.saveresult') !!}</li>
					<li>{!! trans('help/help-mainpages.result.info.domainnewsearch') !!}</li>
					<li>{!! trans('help/help-mainpages.result.info.hideresult') !!}</li>
				</ul>
			</div>
        <h3 id="einstellungen">{!! trans('help/help-mainpages.settings.title') !!}</h3>
            <ul>
                <li>{!! trans('help/help-mainpages.settings.2') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.3') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.4') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.5') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.6') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.7') !!}</li>
    </section>



@endsection