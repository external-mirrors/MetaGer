<div class="card">
	<h1>{!! trans('plugin-page.default-search') !!}</h1>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-chrome.default-search-v49.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-chrome.default-search-v49.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-chrome.default-search-v49.3') }}</li>
	</ol>
</div>
<div class="card">
	<h1>{{ trans('plugin-page.default-page') }}</h1>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-chrome.default-page-v49.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.3', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
		<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.4') }}</li>
	</ol>
</div>