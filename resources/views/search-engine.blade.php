@extends('layouts.subPages')

@section('title', $title )

@section('content')

	<div>
		<h1 class="page-title">{{ trans('search-engine.head.1') }}</h1>
		<div class="card-heavy">
			<h2>{{ trans('search-engine.head.2') }}</h2>
			<p>{{ trans('search-engine.text.1') }}</p>
		</div>
		<div class="card-heavy">
			<h2>{{ trans('search-engine.head.3') }}</h2>		
			<p>{{ trans('search-engine.text.2.1') }}</p>
			<p>{{ trans('search-engine.text.2.2') }}</p>
		</div>
		<div class="card-heavy">
			<h2>{{ trans('search-engine.head.4') }}</h2>
			<p>{{ trans('search-engine.text.3') }}</p>
		</div>
		<div class="card-heavy">
		<h2>{{ trans('search-engine.head.5') }}</h2>
			<p>{{ trans('search-engine.text.4') }}</p>
		</div>
		<div class="card-heavy">
		<p>{{trans('search-engine.text.6')}}</p>
		</div>
        <div class="card-heavy">
		<h2>{{ trans('search-engine.head.7') }}</h2>
			<p>{{ trans('search-engine.text.4') }}</p>
		</div>
        <div class="card-heavy">
		<h2>{{ trans('search-engine.head.8') }}</h2>
			<p>{{ trans('search-engine.text.4') }}</p>
		</div>
        <div class="card-heavy">
		<h2>{{ trans('search-engine.head.9') }}</h2>
			<p>{{ trans('search-engine.text.4') }}</p>
		</div>
        <div class="card-heavy">
		<h2>{{ trans('search-engine.head.10') }}</h2>
			<p>{{ trans('search-engine.text.4') }}</p>
		</div>

	</div>
@endsection
