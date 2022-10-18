@extends('layouts.subPages')

@section('title', $title )

@section('content')

<div>
<h1 class="page-title">{{ trans('prevention-information.head.1') }}</h1>
</div>
<div class="card">
	<h2>{{ trans('prevention-information.head.2') }}</h2>
	<p>{{ trans('prevention-information.text.1') }}</p>

</div>
<div class="card">
	<h1>{{ trans('prevention-information.europe') }}</h1>

	<h2>{{ trans('prevention-information.belgium') }}</h2>
	<p>{!! trans('prevention-information.belgium.1') !!}</p>

	<h2>{{ trans('prevention-information.germany') }}</h2>
	<p>{!! trans('prevention-information.germany.1') !!}</p>

	<h2>{{ trans('prevention-information.denmark') }}</h2>
	<p>{!! trans('prevention-information.denmark.1') !!}</p>

	<h2>{{ trans('prevention-information.france') }}</h2>
	<p>{!! trans('prevention-information.france.1') !!}</p>

	<h2>{{ trans('prevention-information.greece') }}</h2>
	<p>{!! trans('prevention-information.greeece.1') !!}</p>

	<h2>{{ trans('prevention-information.italy') }}</h2>
	<p>{!! trans('prevention-information.italy.1') !!}</p>

</div>
@endsection