@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')

<div role="dialog">
    <h1 class="page-title">{{ trans('plugin-page.head.4') }}</h1>
    <div class="card-heavy">
	    <h3>{{ trans('plugin-page.default-search') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-ie.default-search-v9.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-ie.default-search-v9.2') }}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-ie.default-page-v9.1') !!}</li>
			<li>{!! trans('plugin-desktop/desktop-ie.default-page-v9.2') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-ie.default-page-v9.3') }}</li>
		</ol>
	</div>

@endsection