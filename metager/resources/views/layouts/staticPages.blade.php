<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}">

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
	<link href="/favicon.ico" rel="icon" type="image/x-icon" />
	<link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	@foreach(LaravelLocalization::getSupportedLocales() as $locale => $locale_data)
	@if(LaravelLocalization::getCurrentLocale() !== $locale)
	<link rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedUrl($locale, null, [], true) }}">
	@endif
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
	@if(empty(Cookie::get('key')))
	<link rel="search" type="application/opensearchdescription+xml" title="{{ trans('staticPages.opensearch') }}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action([App\Http\Controllers\StartpageController::class, 'loadPlugin'])) }}">
	@else
	<link rel="search" type="application/opensearchdescription+xml" title="{{ trans('staticPages.opensearch') }}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action([App\Http\Controllers\StartpageController::class, 'loadPlugin'], ['key' => Cookie::get('key')])) }}">
	@endif

	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
	@if (isset($css) && is_array($css))
	@foreach($css as $cssFile)
	<link href="{{ $cssFile }}" rel="stylesheet" />
	@endforeach
	@endif
	@if(isset($page) && $page === 'startpage')
	<meta http-equiv="onion-location" content="http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/{{LaravelLocalization::getCurrentLocale()}}" />
	@endif
	@if(Cookie::get('dark_mode') === "2")
	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager-dark.css') }}" />
	@if(!empty($darkcss) && is_array($darkcss))
	@foreach($darkcss as $cssFile)
	<link rel="stylesheet" type="text/css" href="{{ $cssFile }}" />
	@endforeach
	@endif
	@elseif(Cookie::get('dark_mode') === "1")
	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
	@else
	<link type="text/css" rel="stylesheet" media="(prefers-color-scheme:dark)" href="{{ mix('css/themes/metager-dark.css') }}" />
	@if(!empty($darkcss) && is_array($darkcss))
	@foreach($darkcss as $cssFile)
	<link rel="stylesheet" type="text/css" media="(prefers-color-scheme:dark)" href="{{ $cssFile }}" />
	@endforeach
	@endif
	@endif
	<link type="text/css" rel="stylesheet" href="{{ mix('css/utility.css') }}" />
	<link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">
	<script src="{{ mix('js/utility.js') }}"></script>
	@if(!empty($js) && is_array($js))
	@foreach($js as $jsFile)
	<script src="{{$jsFile}}" defer></script>
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
