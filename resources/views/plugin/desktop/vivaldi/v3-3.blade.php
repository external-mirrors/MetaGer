<h1 class="page-title">{{ trans('plugin-page.head.7') }}</h1>
<div class="card">
	<h1>{{ trans('plugin-page.default-search') }}</h1>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.3') }}</li>
	</ol>
	@include('parts.searchbar', ['class' => 'startpage-searchbar'])
</div>
<div class="card">
	<h4>{{ trans('plugin-page.default-page') }}</h4>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-vivaldi.default-page-v3-3.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-vivaldi.default-page-v3-3.2', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
	</ol>
</div>