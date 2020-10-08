@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.5') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-edge.default-search-v15.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.3') }}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.4') }}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-edge.default-page-v15.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-edge.default-page-v15.2') }}</li>
			<li>{!!trans('plugin-desktop/desktop-edge.default-page-v15.3') !!}</li>
		</ol>
	</div>

@endsection