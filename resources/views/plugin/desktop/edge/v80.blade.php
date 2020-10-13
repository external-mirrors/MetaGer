<div class="card-heavy">
    <h3>{!! trans('plugin-page.default-search') !!}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-edge.default-search-v80.1') !!}</li>
		<li>{!! trans('plugin-desktop/desktop-edge.default-search-v80.2') !!}</li>
	</ol>
</div>
<div class="card-heavy">
	<h3>{{ trans('plugin-page.default-page') }}</h3>
	<ol>
		<li>{!! trans('plugin-desktop/desktop-edge.default-page-v80.1') !!}</li>
		<li>{{ trans('plugin-desktop/desktop-edge.default-page-v80.2', ['link' => LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/")]) }}</li>
		<li>{!! trans('plugin-desktop/desktop-edge.default-page-v80.3') !!}</li>
	</ol>
</div>