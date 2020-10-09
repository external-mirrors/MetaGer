   <div class="card-heavy">
    <h3>{!! trans('plugin-page.default-search') !!}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-search-v57.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.3') }}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.4') }}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.5') }}</li>
	</ol>
</div>
   <div class="card-heavy">
	<h3>{{ trans('plugin-page.default-page') }}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-firefox.default-page-v52.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v52.2', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
	</ol>
</div>