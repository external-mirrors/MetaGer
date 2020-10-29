@extends('layouts.subPages')

@section('title', $title )

@section('content')
	<div class="card-medium">
		<h1>{{ trans('websearch.head.1') }}</h1>
		<p>{{ trans('websearch.head.2') }}</p>
		<h2>{{ trans('websearch.head.3') }}</h2>
		{!! $template !!}
	</div>
	<div class="card-medium">
		<h2>{{ trans('websearch.head.7') }} <button id="copyButton" class="btn btn-default" type="button"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ trans('websearch.head.copy') }}</button></h2>
		<textarea id="codesnippet" readonly style="width:100%;height:500px">
			{{ $template }}
		</textarea>
		<script src="{{ mix('js/widgets.js') }}"></script>
	</div>
@endsection
