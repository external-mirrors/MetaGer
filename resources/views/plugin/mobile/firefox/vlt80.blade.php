@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.1') }}</h1>
	<div class="card">
		<h1>{!! trans('plugin-page.default-search') !!}</h1>
		<ol>
			<li>{{ trans('plugin-mobile/mobile-firefox.default-search-vlt80.1') }}</li>
			<li>{{ trans('plugin-mobile/mobile-firefox.default-search-vlt80.2') }}</li>
			<li>{{ trans('plugin-mobile/mobile-firefox.default-search-vlt80.3') }}</li>
		</ol>
		@include('parts.searchbar', ['class' => 'startpage-searchbar'])
	</div>

	@endsection