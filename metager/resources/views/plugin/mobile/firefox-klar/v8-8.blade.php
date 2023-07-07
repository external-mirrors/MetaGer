@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.8') }}</h1>
	<div class="card">
		<h1>{!! trans('plugin-page.default-search') !!}</h1>
		<ol>
			<li>{!! trans('plugin-mobile/mobile-firefox-klar.default-search-v8-8.1') !!}</li>
			<li>{{ trans('plugin-mobile/mobile-firefox-klar.default-search-v8-8.2') }}</li>
			<li>{{ trans('plugin-mobile/mobile-firefox-klar.default-search-v8-8.3') }}</li>
			<li>{{ trans('plugin-mobile/mobile-firefox-klar.default-search-v8-8.4') }}</li>
			<li style="list-style:none;">{{ trans('plugin-mobile/mobile-firefox.search-string') }}</li>
			<code>{{ route("resultpage", ["eingabe" => ""]) }}%s</code>
		</ol>
	</div>

	@endsection