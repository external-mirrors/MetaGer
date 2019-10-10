@extends('layouts.staticPages', ['page' => 'startpage'])

@section('title', $title )

@section('content')
	<h1 id="startpage-logo">
		<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}">
			<img src="/img/metager.svg" alt="MetaGer" />
		</a>
	</h1>
	@include('parts.searchbar', ['class' => 'startpage-searchbar'])
	<div id="plugin-btn-div">
		<a id="plugin-btn" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/plugin") }}" title="{{ trans('index.plugin-title') }}"><i class="fa fa-plug" aria-hidden="true"></i> {{ trans('index.plugin') }}</a>
	</div>
		<div id="about-us">
			<div class="m-row">
				<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "about") }}">
					<img alt="lock" src="/img/metager-schloss.svg">
					<span>@lang('index.about.1.1')</span>
					<div class="teaser">@lang('index.about.1.2')</div>
				</a>
				@if(LaravelLocalization::getCurrentLocale() === "de")
				<a href="https://suma-ev.de" target="_blank">
				@else
				<a href="https://suma-ev.de/en/index.html" target="_blank">
				@endif
					<img alt="rainbow" src="/img/rainbow.svg">
					<span>@lang('index.about.2.1')</span>
					<div class="teaser">@lang('index.about.2.2')</div>
				</a>
			</div>
			<div class="m-row">
				<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "spende") }}">
					<i class="fas fa-leaf" id="green-leaf"></i>
					<span>@lang('index.about.3.1')</span>
					<div class="teaser">@lang('index.about.3.2')</div>
				</a>
				<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "beitritt") }}">
					<i class="fas fa-heart" id="gradient"></i>
					<span>@lang('index.about.4.1')</span>
					<div class="teaser">@lang('index.about.4.2')</div>
				</a>
			</div>
		</div>
	<a id="scroll-helper" href="#about-us">
		<i class="fas fa-angle-double-down"></i>
	</a>
@endsection
