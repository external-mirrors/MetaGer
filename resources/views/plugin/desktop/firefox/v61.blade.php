@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.1') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-firefox.plugin') !!}</li>
			<li>{!! trans('plugin-desktop/desktop-firefox.default-search-v61.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v61.2') }}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-firefox.default-page-v61.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.3') }}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.4') }}</li>
		</ol>
	</div>

@endsection