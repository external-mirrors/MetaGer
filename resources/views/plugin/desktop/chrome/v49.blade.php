@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')

<div role="dialog">
    <h1 class="page-title">{{ trans('plugin-page.head.2') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-chrome.default-search-v49.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-chrome.default-search-v49.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-chrome.default-search-v49.3') }}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-chrome.default-page-v49.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.3') }}</li>
			<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.4') }}</li>
		</ol>
	</div>

@endsection