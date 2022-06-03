@if(!\app()->make(\App\SearchSettings::class)->header_printed)
<!DOCTYPE html>
<html lang="{!! trans('staticPages.meta.language') !!}">

<head>
	<meta charset="utf-8">
	<link href="/favicon.ico" rel="icon" type="image/x-icon" />
	<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	@foreach(scandir(public_path("img/favicon")) as $file)
	@if(in_array($file, [".", ".."]))
	@continue
	@endif
	@php
	preg_match("/(\d+)\.png$/", $file, $matches);
	@endphp
	@if($matches)
	<link rel="icon" sizes="{{$matches[1]}}x{{$matches[1]}}" href="/img/favicon/{{$file}}" type="image/png">
	<link rel="apple-touch-icon" sizes="{{$matches[1]}}x{{$matches[1]}}" href="/img/favicon/{{$file}}" type="image/png">
	@endif
	@endforeach
	@if(empty(Cookie::get('key')))
	<link rel="search" type="application/opensearchdescription+xml" title="{{ trans('staticPages.opensearch') }}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('StartpageController@loadPlugin')) }}">
	@else
	<link rel="search" type="application/opensearchdescription+xml" title="{{ trans('staticPages.opensearch') }}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('StartpageController@loadPlugin', ['key' => Cookie::get('key')])) }}">
	@endif
	@if(empty(Cookie::get('key')))
	<link rel="search" type="application/opensearchdescription+xml" title="{{ trans('staticPages.opensearch') }}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('StartpageController@loadPlugin')) }}">
	@else
	<link rel="search" type="application/opensearchdescription+xml" title="{{ trans('staticPages.opensearch') }}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('StartpageController@loadPlugin', ['key' => Cookie::get('key')])) }}">
	@endif
	<link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">


	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
	@if(Cookie::get('dark_mode') === "2")
	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager-dark.css') }}" />
	@elseif(Cookie::get('dark_mode') === "1")
	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
	@elseif(Request::input('out', '') !== "results-with-style" )
	<link type="text/css" rel="stylesheet" media="(prefers-color-scheme:dark)" href="{{ mix('css/themes/metager-dark.css') }}" />
	@endif

	@endif
	<title>{{ $eingabe }} - MetaGer</title>
	<meta content="width=device-width, initial-scale=1.0, user-scalable=no" name="viewport" />
	<meta name="p" content="{{ getmypid() }}" />
	<meta name="q" content="{{ $eingabe }}" />
	<meta name="l" content="{{ LaravelLocalization::getCurrentLocale() }}" />
	<meta name="mm" content="{{ \app()->make(\App\Models\HumanVerification::class)->uid }}" />
	<meta name="mn" content="{{ \app()->make(\App\Models\HumanVerification::class)->getVerificationCount() }}" />
	<meta name="searchkey" content="{{ $metager->getSearchUid() }}" />
	<meta name="referrer" content="origin">
	<meta name="age-meta-label" content="age=18" />
	@include('parts.utility')
</head>

<body id="resultpage-body" @if(!empty($imagesearch) && $imagesearch)class="imagesearch" @endif>
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