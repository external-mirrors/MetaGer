@section('content')

<div role="dialog">
	<h1 class="page-title">{{ trans('plugin-page.head.3') }}</h1>
    <div class="card-heavy">
	    <h3>{{ trans('plugin-page.default-page') }}</h3>
		<ol>
			<li>{!! trans('plugin-page/mobile-unable.php') !!}</li>
		</ol>
	</div>
    <div class="card-heavy">
	    <h3>{!! trans('plugin-page.default-page') !!}</h3>
		<ol>
			<li>{!! trans('plugin-mobile/mobile-opera.default-page-v60.1') !!}</li>
			<li>{{ trans('plugin-mobile/mobile-opera.default-page-v60.2') }}</li>
            <li>{{ trans('plugin-mobile/mobile-opera.default-page-v60.3') }}</li>
            <li>{{ trans('plugin-mobile/mobile-opera.default-page-v60.4') }}</li>
            <li>{{ trans('plugin-mobile/mobile-opera.default-page-v60.5') }}</li>
		</ol>
	</div>

@endsection