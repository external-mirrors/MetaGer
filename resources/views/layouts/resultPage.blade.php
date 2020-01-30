<!DOCTYPE html>
<html lang="{!! trans('staticPages.meta.language') !!}">
	<head>
		<meta charset="utf-8">
		<title>{{ $eingabe }} - MetaGer</title>
		<link href="/favicon.ico" rel="icon" type="image/x-icon" />
		<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
		<link rel="apple-touch-icon" href="/img/apple/touch-icon.png">
		<link rel="apple-touch-icon" sizes="57x57" href="/img/apple/touch-icon-57.png">
		<link rel="apple-touch-icon" sizes="72x72" href="/img/apple/touch-icon-72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="/img/apple/touch-icon-76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="/img/apple/touch-icon-114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="/img/apple/touch-icon-120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="/img/apple/touch-icon-144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="/img/apple/touch-icon-152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="/img/apple/touch-icon-180.png">
		<meta content="width=device-width, initial-scale=1.0, user-scalable=no" name="viewport" />
		<meta name="p" content="{{ getmypid() }}" />
		<meta name="q" content="{{ $eingabe }}" />
		<meta name="l" content="{{ LaravelLocalization::getCurrentLocale() }}" />
		<meta name="mm" content="{{ $metager->getVerificationId() }}" />
		<meta name="mn" content="{{ $metager->getVerificationCount() }}" />
		<meta name="searchkey" content="{{ $metager->getSearchUid() }}" />
		<link rel="search" type="application/opensearchdescription+xml" title="{!! trans('resultPage.opensearch') !!}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('StartpageController@loadPlugin')) }}">
		<link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">
		<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome.css') }}" />
		<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome-solid.css') }}" />
		<link type="text/css" rel="stylesheet" href="{{ mix('css/bootstrap.css') }}" />
		<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
		<meta name="referrer" content="origin">
		<meta name="age-meta-label" content="age=18"/>
		@include('parts.utility')
	</head>
	<body id="resultpage-body">
		@if(Request::getHttpHost() === "metager3.de")
		<div class="alert alert-info metager3-unstable-warning-resultpage">
			{!! @trans('resultPage.metager3') !!}
		</div>
		@endif
		@if( !isset($suspendheader) )
			@include('layouts.researchandtabs')
		@else
			<link rel="stylesheet" href="/css/noheader.css">
			<div id="resultpage-container-noheader">
				<div id="results-container">
					<span name="top"></span>
					@include('parts.errors')
					@include('parts.warnings')
					@yield('results')
					<div id="backtotop"><a href="#top">@lang('results.backtotop')</a></div>
				</div>
			</div>
			@include('parts.footer', ['type' => 'resultpage', 'id' => 'resultPageFooter'])
		@endif
		@include('parts.sidebar', ['id' => 'resultPageSideBar'])
		@include('parts.sidebar-opener', ['class' => 'fixed'])
		<script src="{{ mix('js/lib.js') }}"></script>
		<script src="{{ mix('js/scriptResultPage.js') }}" defer></script>
	</body>
</html>
