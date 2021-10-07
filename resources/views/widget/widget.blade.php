@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">{{ trans('widget.head') }}</h1>
<div class="card">
	<p>{{ trans('widget.body.1') }}</p>
	<p id="widgetLinks"><a class="btn btn-default" href="websearch/">{{ trans('widget.body.2') }}</a><a class="btn btn-default" href="sitesearch/">{{ trans('widget.body.3') }}</a></p>
	<p>{{ trans('widget.body.4') }}</p>
</div>
@endsection