@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
	<h1 class="page-title">{!! trans('help/help-mainpages.title') !!}</h1>

	<section id="startpage">
		<h2>{!! trans('help/help-mainpages.title.2') !!}</h2>
		<h3>{!! trans('help/help-mainpages.startpage.title') !!}</h3>
		<p>{!! trans('help/help-mainpages.startpage.info') !!}</p>
		<h3>{!! trans('help/help-mainpages.searchfield.title') !!}</h3>
		<div>
			<p>{!! trans('help/help-mainpages.searchfield.info') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/help-mainpages.searchfield.memberkey') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.slot') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.search') !!}</li>
			</ul>
		</div>
        <h3>{!! trans('help/help-mainpages.resultpage.title') !!}</h3>
		    <div>
			    <ul class="dotlist">
				    <li>{!! trans('help/help-mainpages.resultpage.foci') !!}</li>
				    <li>{!! trans('help/help-mainpages.resultpage.choice') !!}</li>
				        <ul class="dotlist">
				        	<li>{!! trans('help/help-mainpages.resultpage.filter') !!}</li>
				        	<li id="difset">{!! trans('help/help-mainpages.resultpage.settings') !!}</li>
            </div>
        <h3>{!! trans('help/help-mainpages.settings.title') !!}</h3>
            <ul>
                <li>{!! trans('help/help-mainpages.settings.1') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.2') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.3') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.4') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.5') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.6') !!}</li>
                <li>{!! trans('help/help-mainpages.settings.7') !!}</li>
    </section>



@endsection