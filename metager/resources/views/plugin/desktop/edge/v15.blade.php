<div class="card">
	<h1>{!! trans('plugin-page.default-search') !!}</h1>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-edge.default-search-v15.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.3') }}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.4') }}</li>
	</ol>
</div>
<div class="card">
	<h1>{{ trans('plugin-page.default-page') }}</h1>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-edge.default-page-v15.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-page-v15.2', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
		<li>{!!trans('plugin-desktop/desktop-edge.default-page-v15.3') !!}</li>
	</ol>
</div>