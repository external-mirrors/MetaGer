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
	<link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">
	<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome.css') }}" />
	<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome-solid.css') }}" />

	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}"/>
	@if(Cookie::get('dark_mode') === "2")
		<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager-dark.css') }}"/>
	@elseif(Cookie::get('dark_mode') === "1")
		<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}"/>
	@elseif(Request::input('out', '') !== "results-with-style" )
		<link type="text/css" rel="stylesheet" media="(prefers-color-scheme:dark)" href="{{ mix('css/themes/metager-dark.css') }}"/>
	@endif
	
