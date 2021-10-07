@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.5') }}</h1>
	<div class="card">
		<h1>{!! trans('plugin-page.default-search') !!}</h1>
		<ol>
			<li>{!! trans('plugin-mobile/mobile-edge.default-search-v45.1') !!}</li>
			<li>{{ trans('plugin-mobile/mobile-edge.default-search-v45.2') }}</li>
			<li>{{ trans('plugin-mobile/mobile-edge.default-search-v45.3') }}</li>
			<li>{{ trans('plugin-mobile/mobile-edge.default-search-v45.4') }}</li>
			<li><small>{{ trans('plugin-mobile/mobile-edge.default-search-v45.5') }}</small></li>
		</ol>
		@include('parts.searchbar', ['class' => 'startpage-searchbar'])
	</div>
	<div class="card">
		<h1>{{ trans('plugin-page.default-page') }}</h1>
		<ol>
			<li>{!! trans('plugin-mobile/mobile-edge.default-page-v45.1') !!}</li>
			<li>{!! trans('plugin-mobile/mobile-edge.default-page-v45.2') !!}</li>
			<li>{{ trans('plugin-mobile/mobile-edge.default-page-v45.3') }}</li>
			<li>{{ trans('plugin-mobile/mobile-edge.default-page-v45.4') }}</li>
		</ol>
	</div>

	@endsection