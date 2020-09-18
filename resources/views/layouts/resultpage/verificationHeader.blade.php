<!DOCTYPE html>
<html lang="{!! trans('staticPages.meta.language') !!}">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/index.css?id={{ $key }}">
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
	<link rel="search" type="application/opensearchdescription+xml" title="{!! trans('resultPage.opensearch') !!}" href="{{  LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('StartpageController@loadPlugin')) }}">
	<link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">
	<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome.css') }}" />
	<link type="text/css" rel="stylesheet" href="{{ mix('css/fontawesome-solid.css') }}" />
	<link type="text/css" rel="stylesheet alternate" href="{{ mix('css/themes/metager-dark.css') }}" title="MetaGer Dark"/>
	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" title="MetaGer"/>
