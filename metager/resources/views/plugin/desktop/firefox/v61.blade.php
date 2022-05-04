<div class="card">
	<h1>{!! trans('plugin-page.firefox-plugin') !!}</h1>
	<p>
		{!! trans('plugin-desktop/desktop-firefox.plugin') !!}
	</p>
</div>
<div class="card">
	<h1>{!! trans('plugin-page.firefox-default-search') !!}</h1>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-search-v61.1') !!}</li>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-search-v61.2') !!}</li>
	</ol>
</div>
<div class="card">
	<h1>{{ trans('plugin-page.default-page') }}</h1>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-page-v61.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.3') }}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.4', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
	</ol>
</div>