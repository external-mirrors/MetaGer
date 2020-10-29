@extends('layouts.subPages')

@section('title', $title )

@section('content')
	<div class="card-medium">
		<h1>{{ trans('sitesearch.head.1') }}</h1>
		<p>{{ trans('sitesearch.head.2') }}</p>
		<h2>{{ trans('sitesearch.head.3') }}</h2>
		<form method="GET" action="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/sitesearch/") }}" accept-charset="UTF-8">
			<div class="input-group">
				<input type="text"  class="form-control" name="site" placeholder="{{ trans('sitesearch.head.4') }}" required="" value="{{ $site }}">
				<span class="input-group-btn">
					<button class="btn btn-default" type="submit">{{ trans('sitesearch.head.5') }}</button>
				</span>
			</div>
		</form>
	@if ($site !== '')
		<h2>{{ trans('sitesearch.generated.1') }}</h2>
		{!! $template !!}
	</div>
	<div class="card-medium">
		<h2>{{ trans('sitesearch.generated.5') }} <button id="copyButton" class="btn btn-default" type="button"><i class="fa fa-paperclip" aria-hidden="true"></i> {{ trans('websearch.head.copy') }}</button></h2>
		<textarea id="codesnippet" readonly style="width:100%;height:500px">
			{!! $template !!}
		</textarea>
	@else
	</div>
	@endif
	<script src="{{ mix('js/widgets.js') }}"></script>
@endsection
