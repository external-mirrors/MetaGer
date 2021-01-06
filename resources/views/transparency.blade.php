@extends('layouts.subPages')

@section('title', $title )

@section('content')

	<div>
		<h1 class="page-title">{{ trans('transparency.head.1') }}</h1>
		<div class="card-heavy">
			<h2>{{ trans('transparency.head.2') }}</h2>
			<p>{{ trans('transparency.text.1') }}</p>
		</div>
		<div class="card-heavy">
			<h2>{{ trans('transparency.head.3') }}</h2>
			<picture>
            <source media="(prefers-color-scheme:dark)" srcset="/img/transparency-meatindex-dark-mode.svg">
					<img src="/img/transparency-metaindex.svg" id="transparency-metaindex-img">	
          </picture>
			
			<p>{{ trans('transparency.text.2.1') }}</p>
			<p>{{ trans('transparency.text.2.2') }}</p>
		</div>
		<div class="card-heavy">
			<h2>{{ trans('transparency.head.4') }}</h2>
			<p>{{ trans('transparency.text.3') }}</p>
		</div>
		<div class="card-heavy">
		<h2>{{ trans('transparency.head.5') }}</h2>
			<p>{{ trans('transparency.text.4') }}</p>
		</div>
		<div class="card-heavy">
		<p>{{ trans('transparency.text.5') }}</p>
		</div>
	</div>
@endsection
