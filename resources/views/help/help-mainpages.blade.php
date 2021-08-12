@extends('layouts.subPages', ['page' => 'hilfe'])

@section('title', $title )

@section('content')
	<div class="alert alert-warning" role="alert">{!! trans('help/help.achtung') !!}</div>
	<h1 class="page-title">{!! trans('help/help-mainpages.title') !!}</h1>

	<section class="card-heavy">
		<h2>{!! trans('help/help-mainpages.title.2') !!}</h2>
		<h3>{!! trans('help/help-mainpages.startpage.title') !!}</h3>
		<p>{!! trans('help/help-mainpages.startpage.info') !!}</p>
		<h3>{!! trans('help/help-mainpages.searchfield.title') !!}</h3>
		<div>
			<p>{!! trans('help-mainpages.searchfield.info') !!}</p>
			<ul class="dotlist">
				<li>{!! trans('help/help-mainpages.searchfield.memberkey') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.slot') !!}</li>
				<li>{!! trans('help/help-mainpages.searchfield.search') !!}</li>
			</ul>
		</div>