<div class="card">
	<h1>{{ trans('plugin-page.default-search') }}</h1>
	<ol>
		<li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.1') }}</li>
		<li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.3') }}</li>
		<li style="list-style:none;"><small>{!! trans('plugin-page.desktop-unable') !!}</small></li>
	</ol>
	@include('parts.searchbar', ['class' => 'startpage-searchbar'])
</div>
<div class="card">
	<h1>{{ trans('plugin-page.default-page') }}</h1>
	<ol>
		<li>{{ trans('plugin-desktop/desktop-opera.default-page-v36.1') }}</li>
		<li>{{ trans('plugin-desktop/desktop-opera.default-page-v36.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-opera.default-page-v36.3', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
		<li>{!! trans('plugin-desktop/desktop-opera.default-page-v36.3') !!}</li>
	</ol>
</div>