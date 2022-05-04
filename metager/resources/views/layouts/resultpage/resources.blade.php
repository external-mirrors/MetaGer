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