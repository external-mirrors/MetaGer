@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')

<div class="card-heavy">
	<h1 class="page-title">{{ trans('plugin-page.head.7') }}</h1>
	<h3>{{ trans('plugin-page.default-search') }}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.3') }}</li>
		<li>{{ trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.4') }}</li>
	</ol>
</div>
<div class="card-heavy">
	<h4>{{ trans('plugin-page.default-page') }}</h4>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-vivaldi.default-page-v3-3.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-vivaldi.default-page-v3-3.2') }}</li>
	</ol>
</div>
@endsection