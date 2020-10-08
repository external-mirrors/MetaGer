@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.1') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.1') }}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.3') }}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.4') }}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-firefox.default-page-v52.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v52.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v52.3') }}</li>
			<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v52.4') }}</li>
		</ol>
	</div>

@endsection