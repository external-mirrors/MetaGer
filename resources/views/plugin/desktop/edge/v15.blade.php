<div class="card-heavy">
    <h3>{!! trans('plugin-page.default-search') !!}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-edge.default-search-v15.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.2') }}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.3') }}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-search-v15.4') }}</li>
	</ol>
</div>
<div class="card-heavy">
	<h3>{{ trans('plugin-page.default-page') }}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-edge.default-page-v15.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-page-v15.2', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
		<li>{!!trans('plugin-desktop/desktop-edge.default-page-v15.3') !!}</li>
	</ol>
</div>