@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')

<div role="dialog">
    <h1 class="page-title">{{ trans('plugin-page.head.6') }}</h1>
    <div class="card-heavy">
	    <h3>{{ trans('plugin-page.default-search') }}</h3>
		<ol>
			<li style="list-style:none;">{!! trans('plugin-page.desktop-unable') !!}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/desktop-safari.default-page-v10.1') !!}</li>
			<li>{{ trans('plugin-desktop/desktop-safari.default-page-v10.2') }}</li>
			<li>{{ trans('plugin-desktop/desktop-safari.default-page-v10.3') }}</li>
		</ol>
	</div>

@endsection