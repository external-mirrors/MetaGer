@extends('layouts.subPages')

@section('title', $title )

@section('content')

<div>
	<h1 class="page-title">{{ trans('transparency.head.1') }}</h1>
	<div class="card">
		<h2>{{ trans('transparency.head.2') }}</h2>
		<p>{!! trans('transparency.text.1', ["sourcecode" => "https://gitlab.metager.de/open-source/MetaGer", "license" => "https://gitlab.metager.de/open-source/MetaGer/-/blob/development/LICENSE", "sumalink" => "https://suma-ev.de"]) !!}</p>
	</div>
	<div class="card">
		<h2>{{ trans('transparency.head.3') }}</h2>
		<img src="/img/transparency-metaindex.svg" id="transparency-metaindex-img">

		<p>{{ trans('transparency.text.2.1') }}</p>
		<p>{{ trans('transparency.text.2.2') }}</p>
	</div>
	<div class="card">
		<h2>{{ trans('transparency.head.4') }}</h2>
		<p>{{ trans('transparency.text.3') }}</p>
	</div>
	<div class="card">
		<h2>{{ trans('transparency.head.5') }}</h2>
		<p>{{ trans('transparency.text.4') }}</p>
	</div>
	<div class="card">
		<p>{!! trans('transparency.text.5', ["contact" => LaravelLocalization::getLocalizedUrl(LaravelLocalization::getCurrentLocale(), route('contact'))]) !!}</p>
	</div>
</div>
@endsection