<div class="card">
	<h1>{!! trans('plugin-page.default-search') !!}</h1>
	<ol>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.1') }}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.2') }}</li>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-search-v52.3') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.4') }}</li>
	</ol>
</div>
<div class="card">
	<h1>{{ trans('plugin-page.default-page') }}</h1>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-page-v52.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v52.2', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
	</ol>
</div>