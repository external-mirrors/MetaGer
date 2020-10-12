@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.5') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/mobile-edge.default-search-v45.1') !!}</li>
			<li>{{ trans('plugin-desktop/mobile-edge.default-search-v45.2') }}</li>
			<li>{{ trans('plugin-desktop/mobile-edge.default-search-v45.3') }}</li>
		</ol>
	</div>
    <div class="card-heavy">
		<h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-desktop/mobile-edge.default-page-v45.1') !!}</li>
			<li>{!! trans('plugin-desktop/mobile-edge.default-page-v45.2') !!}</li>
			<li>{{ trans('plugin-desktop/mobile-edge.default-page-v45.3') }}</li>
			<li>{{ trans('plugin-desktop/mobile-edge.default-page-v45.4') }}</li>
		</ol>
	</div>

@endsection