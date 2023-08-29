<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}">

<head>
	<meta charset="utf-8">
	@foreach(LaravelLocalization::getSupportedLocales() as $locale => $locale_data)
	@if(LaravelLocalization::getCurrentLocale() !== $locale)
	<link rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedUrl($locale, null, [], true) }}">
	@endif
	@endforeach
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
	<script src="{{ mix('js/scriptResultPage.js') }}" defer></script>

	<title>{{ $eingabe }} - MetaGer</title>
	<meta content="width=device-width, initial-scale=1.0, user-scalable=no" name="viewport" />
	<meta name="p" content="{{ getmypid() }}" />
	<meta name="q" content="{{ $eingabe }}" />
	<meta name="l" content="{{ App\Localization::getLanguage() }}" />
	<meta name="hv" content="{{ app()->make(\App\Models\Verification\HumanVerification::class)->key }}" />
	<meta name="searchkey" content="{{ $metager->getSearchUid() }}" />
	<meta name="referrer" content="origin-when-cross-origin">
	<meta name="age-meta-label" content="age=18" />
	{{-- Add Advertisement Scripts if Yahoo is enabled --}}
	@if(app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine("yahoo") !== null)
	<meta name="source_tag" content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->configuration->getParameter->Partner }}" />
	<meta name="ysid" content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->search_id }}" />
	<meta name="cid" content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->client_id }}" />
	<meta name="ig" content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->impression_guid }}" />
	<meta name="clarityId" content="iiolvwkqcy" />
	<meta name="rguid" content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->rguid }}" />
	<meta name="test_mode" content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->test_mode }}" />
	@endif
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
		@include('parts.enginefooter')
	</div>
	@include('parts.footer', ['type' => 'resultpage', 'id' => 'resultPageFooter'])
	@endif
	@include('parts.sidebar', ['id' => 'resultPageSideBar'])
	@include('parts.sidebar-opener', ['class' => 'fixed'])
	{{-- Add Advertisement Scripts if Yahoo is enabled --}}
	@if(app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine("yahoo") !== null)
	<img height=”1” width=”1” src="https://search.yahoo.com/beacon/geop/p?s=1197774733&ysid={{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->search_id }}&traffic_source={{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->configuration->getParameter->Partner }}" />
	@endif
</body>

</html>