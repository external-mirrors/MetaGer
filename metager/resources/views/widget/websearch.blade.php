@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">{{ trans('websearch.head.1') }}</h1>
<div class="card">

	<p>{{ trans('websearch.head.2') }}</p>
	<h1>{{ trans('websearch.head.3') }}</h1>
	{!! $template_preview !!}
</div>
<div class="card">
	<h1>{{ trans('websearch.head.7') }} <button id="copyButton" class="btn btn-default" type="button"><img class="widget-mg-icon mg-icon" src="/img/icon-paperclip.svg"> {{ trans('websearch.head.copy') }}</button></h1>
	<textarea id="codesnippet" readonly>
	{{ $template_webpage }}
	</textarea>
	<script src="{{ mix('js/widgets.js') }}"></script>
</div>
@endsection