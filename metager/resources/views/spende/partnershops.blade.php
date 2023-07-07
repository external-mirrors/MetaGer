@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.datenschutz', 'class="active"')

@section('content')
<div class="card">
	<h1>{{ trans('partnershops.heading') }}</h1>
	<p>{{ trans('partnershops.paragraph.1') }}</p>
	<p>{!! trans('partnershops.paragraph.2', ["link" => app(\App\Models\Authorization\Authorization::class)->getAdfreeLink()]) !!}</p>
	<p>{!! trans('partnershops.paragraph.3', ["link" => route("beitritt")]) !!}</p>
</div>
@endsection