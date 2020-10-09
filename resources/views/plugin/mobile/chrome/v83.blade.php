@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.2') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/mobile-chrome.default-search-v83.1') !!}</li>
			<li>{{ trans('plugin-desktop/mobile-chrome.default-search-v83.2') }}</li>
			<li>{{ trans('plugin-desktop/mobile-chrome.default-search-v83.3') }}</li>
			<li>{{ trans('plugin-desktop/mobile-chrome.default-search-v83.4') }}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/mobile-chrome.default-page-v83.1') !!}</li>
			<li>{{ trans('plugin-desktop/mobile-chrome.default-page-v83.2') }}</li>
			<li>{{ trans('plugin-desktop/mobile-chrome.default-page-v83.3') }}</li>
			<li>{{ trans('plugin-desktop/mobile-chrome.default-page-v83.4', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
		</ol>
	</div>

@endsection