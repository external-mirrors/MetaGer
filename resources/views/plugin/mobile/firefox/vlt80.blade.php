@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.1') }}</h1>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-search') !!}</h3>
		<ol>
			<li>{{ trans('plugin-desktop/mobile-firefox.default-search-vlt80.1') }}</li>
			<li>{{ trans('plugin-desktop/mobile-firefox.default-search-vlt80.2') }}</li>
		</ol>
		@include('parts.searchbar', ['class' => 'startpage-searchbar'])
	</div>

@endsection