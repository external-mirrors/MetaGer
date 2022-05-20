@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.2') }}</h1>
	<div class="card">
		<h1>{!! trans('plugin-page.default-search') !!}</h1>
		<ol>
			<li>{!! trans('plugin-mobile/mobile-chrome.default-search-v83.1') !!}</li>
			<li>{{ trans('plugin-mobile/mobile-chrome.default-search-v83.2') }}</li>
			<li>{{ trans('plugin-mobile/mobile-chrome.default-search-v83.3') }}</li>
			<li>{{ trans('plugin-mobile/mobile-chrome.default-search-v83.4') }}</li>
			<li><small>{{ trans('plugin-mobile/mobile-chrome.default-search-v83.5') }}</small></li>
		</ol>
		@include('parts.searchbar', ['class' => 'startpage-searchbar'])
	</div>
	<div class="card">
		<h1>{{ trans('plugin-page.default-page') }}</h1>
		<ol>
			<li>{!! trans('plugin-mobile/mobile-chrome.default-page-v83.1') !!}</li>
			<li>{{ trans('plugin-mobile/mobile-chrome.default-page-v83.2') }}</li>
			<li>{{ trans('plugin-mobile/mobile-chrome.default-page-v83.3') }}</li>
			<li>{{ trans('plugin-mobile/mobile-chrome.default-page-v83.4', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
		</ol>
	</div>

	@endsection