<div class="card-heavy">
    <h3>{!! trans('plugin-page.firefox-plugin') !!}</h3>
	<ol>
		<li style="list-style:none;">{!! trans('plugin-desktop/desktop-firefox.plugin') !!}</li>
	</ol>
</div>
<div class="card-heavy">
    <h3>{!! trans('plugin-page.firefox-default-search') !!}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-search-v61.1') !!}</li>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-search-v61.2') !!}</li>
	</ol>
</div>
<div class="card-heavy">
	<h3>{{ trans('plugin-page.default-page') }}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-page-v61.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.3') }}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.4', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
	</ol>
</div>