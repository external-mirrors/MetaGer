@section('content')

<div role="dialog">
	<h1 class="search-title">{{ trans('plugin-page.head.3') }}</h1>
	<div class="card">
		<h1>{{ trans('plugin-page.default-page') }}</h1>
		<ol>
			<li>{!! trans('plugin-page/mobile-unable.php') !!}</li>
		</ol>
	</div>
	<div class="card">
		<h1>{!! trans('plugin-page.default-page') !!}</h1>
		<ol>
			<li>{{ trans('plugin-mobile/mobile-opera.default-search-v60.1') }}</li>
			<li>{{ trans('plugin-mobile/mobile-opera.default-search-v60.2') }}</li>
			<li>{{ trans('plugin-mobile/mobile-opera.default-search-v60.3') }}</li>
			<li>{{ trans('plugin-mobile/mobile-opera.default-search-v60.4') }}</li>
			<li><small>{{ trans('plugin-page.mobile-unable') }}</small></li>
			@include('parts.searchbar', ['class' => 'startpage-searchbar'])
		</ol>
	</div>

	@endsection