<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}"
	@if(app(App\SearchSettings::class)->theme !== "system") data-theme="{{ app(App\SearchSettings::class)->theme }}" @endif>

<head>
	<meta charset="utf-8" />
	<title>@yield('title')</title>
	<meta name="description" content="{!! trans('staticPages.meta.Description') !!}" />
	<meta name="keywords" content="{!! trans('staticPages.meta.Keywords') !!}" />
	<meta name="page-topic" content="Dienstleistung" />
	<meta name="robots" content="index,follow" />
	<meta name="revisit-after" content="7 days" />
	<meta name="audience" content="all" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
	<meta name="statistics-enabled" content="{{ config("metager.matomo.enabled") }}">

	@if(!in_array(app(\App\SearchSettings::class)->suggestion_provider, [null, "off"]))
		<meta name="suggestions-enabled" content="true">
	@endif
	<link href="/favicon.ico" rel="icon" type="image/x-icon" />
	<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	@foreach (App\Localization::getAlternateLocales() as $locale)
		<link rel="alternate" hreflang="{{ $locale }}"
			href="{{ LaravelLocalization::getLocalizedUrl($locale, null, [], true) }}">
	@endforeach
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
	@if(\Auth::guard("key")->user() !== null || app(\App\Models\Authorization\Authorization::class)->canDoAuthenticatedSearch())
		<link rel="search" type="application/opensearchdescription+xml"
			title="{{ \App\Http\Controllers\StartpageController::GET_PLUGIN_SHORT_NAME() }}"
			href="{{  action([App\Http\Controllers\StartpageController::class, 'loadPlugin']) }}">
	@endif
	<link type="text/css" rel="stylesheet" href="{{ Vite::asset('resources/less/metager/metager.less') }}" />
	@if (isset($css) && is_array($css))
		@foreach($css as $cssFile)
			<link href="{{ $cssFile }}" rel="stylesheet" />
		@endforeach
	@endif
	@if(isset($page) && $page === 'startpage')
		<meta http-equiv="onion-location"
			content="http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/{{LaravelLocalization::getCurrentLocale()}}" />
	@endif
	{{-- metager.less carries both palettes and picks between them itself, from the
	     data-theme attribute above and prefers-color-scheme. No second stylesheet,
	     and no script. The $darkcss pages below are the ones not yet converted. --}}
	<meta name="color-scheme" content="{{ app(App\SearchSettings::class)->theme === "dark" ? "dark light" : "light dark" }}">
	@if(!empty($darkcss) && is_array($darkcss) && app(App\SearchSettings::class)->theme !== "light")
		@foreach($darkcss as $cssFile)
			<link rel="stylesheet" type="text/css"
				@if(app(App\SearchSettings::class)->theme !== "dark") media="(prefers-color-scheme:dark)" @endif
				href="{{ $cssFile }}" />
		@endforeach
	@endif
	<link type="text/css" rel="stylesheet" href="{{ Vite::asset('resources/less/utility.less') }}" />
	<link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">
	{{-- type="module" is not optional: Vite emits ES modules, and a bundle that
	     imports a shared chunk is a syntax error when loaded as a classic
	     script, so the file downloads and never runs. Modules defer by
	     default, which is why the explicit defer below is gone. --}}
	<script type="module" src="{{ Vite::asset('resources/js/utility.js') }}"></script>
	@if(!empty($js) && is_array($js))
		@foreach($js as $jsFile)
			<script type="module" src="{{$jsFile}}"></script>
		@endforeach
	@endif
</head>

<body>
	@if(Request::getHttpHost() === "metager3.de")
		<div class="alert alert-info metager3-unstable-warning-static-pages">
			{!! @trans('resultPage.metager3') !!}
		</div>
	@endif
	<header>
		@yield('homeIcon')
	</header>
	<div class="wrapper {{$page ?? ''}}">
		<main id="main-content">
			@if (isset($success))
				<div class="alert alert-success" role="alert">{{ $success }}</div>
			@endif
			@if (isset($info))
				<div class="alert alert-info" role="alert">{{ $info }}</div>
			@endif
			@if (isset($warning))
				<div class="alert alert-warning" role="alert">{{ $warning }}</div>
			@endif
			@if (isset($error))
				<div class="alert alert-danger" role="alert">{{ $error }}</div>
			@endif
			@yield('content')
		</main>
	</div>
	@include('parts.sidebar', ['id' => 'staticPagesSideBar'])
	@include('parts.sidebar-opener', ['class' => 'fixed'])
	@if (isset($page) && $page === 'startpage')
		@include('parts.footer', ['type' => 'startpage', 'id' => 'startPageFooter'])
	@else
		@include('parts.footer', ['type' => 'subpage', 'id' => 'subPageFooter'])
	@endif
</body>

</html>