@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.5') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-edge.default-search-v85.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-search-v85.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-search-v85.3') }}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-search-v85.4') }}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-edge.default-page-v80.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-page-v80.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-page-v80.3') }}</li>
			<li>{!! trans('plugin-desktop/desktop-edge.default-page-v80.4') !!}</li>
		</ol>
	</div>

@endsection