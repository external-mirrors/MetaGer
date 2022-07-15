@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">{{ trans('sitesearch.head.1') }}</h1>
<div class="card">
	<p>{{ trans('sitesearch.head.2') }}</p>
	<h1>{{ trans('sitesearch.head.3') }}</h1>
	<form method="GET" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/sitesearch/") }}" accept-charset="UTF-8">
		<div class="input-group">
			<input type="text" class="form-control" name="site" placeholder="{{ trans('sitesearch.head.4') }}" required="" value="{{ $site }}">
			<span class="input-group-btn">
				<button class="btn btn-default" type="submit">{{ trans('sitesearch.head.5') }}</button>
			</span>
		</div>
	</form>
	@if ($site !== '')
	<h1>{{ trans('sitesearch.generated.1') }}</h1>
	{!! $template_preview !!}
</div>
<div class="card">
	<h1>{{ trans('sitesearch.generated.5') }} <button id="copyButton" class="btn btn-default js-only" type="button"><img class="mg-icon widget-mg-icon " src="/img/icon-paperclip.svg"> {{ trans('websearch.head.copy') }}</button></h1>
	<textarea id="codesnippet" readonly>
			{!! $template_webpage !!}
		</textarea>
	@else
</div>
@endif
<script src="{{ mix('js/widgets.js') }}"></script>
@endsection