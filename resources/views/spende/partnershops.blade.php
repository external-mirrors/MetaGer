@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.datenschutz', 'class="active"')

@section('content')
	<div class="card-heavy">
		<h1>{{ trans('partnershops.heading') }}</h1>
		<p>{{ trans('partnershops.paragraph.1') }}</p>
		<p>{!! trans('partnershops.paragraph.2', ["link" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route("spende"))]) !!}</p>
		<p>{!! trans('partnershops.paragraph.3', ["link" => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route("beitritt"))]) !!}</p>
	</div>
@endsection
