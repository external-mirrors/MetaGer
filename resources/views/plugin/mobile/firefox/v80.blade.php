@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.1') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{!! trans('plugin-mobile/mobile-firefox.default-search-v80.1') !!}</li>
			<li>{{ trans('plugin-mobile/mobile-firefox.default-search-v80.2') }}</li>
            <li>{{ trans('plugin-mobile/mobile-firefox.default-search-v80.3') }}</li>
            <li>{{ trans('plugin-mobile/mobile-firefox.default-search-v80.4') }}</li>
            <li>{!! trans('plugin-mobile/mobile-firefox.default-search-v80.5') !!}</li>
			<li style="list-style:none;">{{ trans('plugin-mobile/mobile-firefox.search-string') }}</li>
			<code>{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route("resultpage", ["eingabe" => ""])) }}%s</code>
		</ol>
	</div>

@endsection