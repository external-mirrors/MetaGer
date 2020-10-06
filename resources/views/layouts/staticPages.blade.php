<!DOCTYPE html>
<html lang="{!! trans('staticPages.meta.language') !!}">
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
		<link rel="apple-touch-icon" href="/img/apple/touch-icon.png">
		<link rel="apple-touch-icon" sizes="57x57" href="/img/apple/touch-icon-57.png">
		<link rel="apple-touch-icon" sizes="72x72" href="/img/apple/touch-icon-72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="/img/apple/touch-icon-76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="/img/apple/touch-icon-114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="/img/apple/touch-icon-120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="/img/apple/touch-icon-144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="/img/apple/touch-icon-152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="/img/apple/touch-icon-180.png">
		@if(empty(Cookie::get('key')))
		<link rel="search" type="application/opensearchdescription+xml" title="{{ trans('staticPages.opensearch') }}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('StartpageController@loadPlugin')) }}">
		@else
		<link rel="search" type="application/opensearchdescription+xml" title="{{ trans('staticPages.opensearch') }}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('StartpageController@loadPlugin', ['key' => Cookie::get('key')])) }}">
		@endif
		<link type="text/css" rel="stylesheet alternate" href="{{ mix('css/themes/metager-dark.css') }}" title="MetaGer Dark"/>
		<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" title="MetaGer"/>
		<link type="text/css" rel="stylesheet" href="{{ mix('css/utility.css') }}" />
		<link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">
		<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome.css') }}" />
		<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome-solid.css') }}" />
		<script src="{{ mix('js/lib.js') }}"></script>
		<script src="{{ mix('js/utility.js') }}"></script>
		@if (isset($css))
			@if(is_array($css))
				@foreach($css as $cssFile)
		<link href="{{ $cssFile }}" rel="stylesheet" />
				@endforeach
			@endif
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
