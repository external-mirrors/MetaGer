@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')
<div role="dialog">
	<h1 class="page-title">
		@if ($browser === 'Firefox' || $browser === 'Mozilla')
		{{ trans('plugin-page.head.1') }}
		@elseif ($browser === 'Chrome')
		{{ trans('plugin-page.head.2') }}
		@elseif ($browser === 'Opera')
		{{ trans('plugin-page.head.3') }}
		@elseif ($browser === 'IE')
		{{ trans('plugin-page.head.4') }}
		@elseif ($browser === 'Edge')
		{{ trans('plugin-page.head.5') }}
		@elseif ($browser === 'Safari')
		{{ trans('plugin-page.head.6') }}
		@elseif ($browser === 'UCBrowser')
		{{ trans('plugin-page.head.9') }}
		@else
		{{ trans('plugin-page.head.0') }}
		@endif
	</h1>
	<div class="card">
		<h1>{{ trans('plugin-page.search-engine.1') }}</h1>
		<p>{{ trans('plugin-page.search-engine.2') }}</p>
	</div>
	{{-- "not mobile" rather than isDesktop(): DeviceDetector reports an
	     unrecognised User-Agent as neither desktop nor mobile, so branching on
	     isDesktop() would drop such a client through both arms and leave it
	     with no instructions at all. jenssegers/agent used to call that case
	     desktop. Matching on !isMobile() reproduces the old outcome for all
	     four cases: known desktop and unknown both take this arm, tablets and
	     phones do not. --}}
	@if (!$agent->isMobile())
	@if ($browser === 'Firefox' || $browser === 'Mozilla')
	@if (version_compare($agent->version(), '89.', '>='))
	@include ('plugin/desktop/firefox/v89')
	@elseif (version_compare($agent->version(), '61.', '>='))
	@include ('plugin/desktop/firefox/v61')
	@elseif (version_compare($agent->version(), '57.', '>='))
	@include ('plugin/desktop/firefox/v57')
	@else
	@include ('plugin/desktop/firefox/v52')
	@endif

	@elseif ($browser === 'Chrome')
	@if (version_compare($agent->version(), '59.', '>='))
	@include ('plugin/desktop/chrome/v59')
	@include ('plugin/desktop/vivaldi/v3-3')
	@elseif (version_compare($agent->version(), '53.', '>='))
	@include ('plugin/desktop/chrome/v53')
	@else
	@include ('plugin/desktop/chrome/v49')
	@endif

	@elseif ($browser === 'Opera')
	@include ('plugin/desktop/opera/v36')

	@elseif ($browser === 'IE')
	@if (version_compare($agent->version(), '11.', '>='))
	@include('plugin/desktop/ie/v11')
	@else
	@include('plugin/desktop/ie/v9')
	@endif

	@elseif ($browser === 'Edge')
	@if (version_compare($agent->version(), '80.', '>='))
	@include('plugin/desktop/edge/v80')
	@elseif (version_compare($agent->version(), '18.', '>='))
	@include('plugin/desktop/edge/v18')
	@elseif (version_compare($agent->version(), '15.', '>='))
	@include('plugin/desktop/edge/v15')
	@endif

	@elseif ($browser === 'Safari')
	@include ('plugin/desktop/safari/v10')

	@else

	@section('content')

	<div class="card">
		<h3>{{ trans('plugin-page.browser-download') }}</h3>
		<p>
			<small>{!! trans('plugin-page.desktop-unlisted.php') !!}</small>
		</p>
	</div>
	@include ('plugin/desktop/firefox/v61')

	@endsection

	@endif

	@elseif ($agent->isPhone())
	@if ($browser === 'Firefox')
	@if (version_compare($agent->version(), '80.') >= 0)
	@include ('plugin/mobile/firefox/v80')
	@include ('plugin/mobile/firefox-klar/v8-8')
	@else
	@include ('plugin/mobile/firefox/vlt80')
	@endif

	@elseif ($browser === 'Chrome')
	@include('plugin/mobile/chrome/v83')

	@elseif ($browser === 'Opera')
	@include ('plugin/mobile/opera/v60')

	@elseif ($browser === 'Edge')
	@include ('plugin/mobile/edge/v45')

	@elseif ($browser === 'Safari' || $browser === 'UCBrowser')

	@section('content')

	<div class="card">
		<h3>{!! trans('plugin-page.browser-download') !!}</h3>
		<p>
			<small>{!! trans('plugin-page.mobile-unable') !!}</small>
		</p>
	</div>
	@include ('plugin/desktop/firefox/v61')

	@endsection

	@else

	@section('content')

	<div class="card">
		<h3>{{ trans('plugin-page.browser-download') }}</h3>
		<ol>
			<li>{!! trans('plugin-page.desktop-unlisted.php') !!}</li>
		</ol>
	</div>
	@include ('plugin/desktop/firefox/v61')

	@endsection

	@endif
	@endif

	@endsection