<div class="card-heavy">
    <h3>{{ trans('plugin-page.default-search') }}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-opera.default-search-v36.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.2') }}</li>
           <li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.3') }}</li>
           <li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.4') }}</li>
		<li style="list-style:none;">{!! trans('plugin-page.desktop-unable') !!}</li>
	</ol>
</div>
<div class="card-heavy">
	<h3>{{ trans('plugin-page.default-page') }}</h3>
	<ol>
		<li>{{ trans('plugin-desktop/desktop-opera.default-page-v36.1') }}</li>
		<li>{{ trans('plugin-desktop/desktop-opera.default-page-v36.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-opera.default-page-v36.3', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
		<li>{!! trans('plugin-desktop/desktop-opera.default-page-v36.3') !!}</li>
	</ol>
</div>