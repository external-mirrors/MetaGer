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
		@else
			$(".seperator").addClass("hidden");
		@endif
	</h1>
	@if ($agent->isDesktop())
		@if ($browser === 'Firefox' || $browser === 'Mozilla')
			<div class="card-medium">
				<h3>{!! trans('plugin-desktop/desktop-firefox.firefox.plugin') !!}</h3>
			</div>
			@if (version_compare($agent->version($browser), '61.', '>='))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search', ['browser' => $browser]) !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-firefox.default-search-v61.1') !!}
							@if(LaravelLocalization::getCurrentLocale() == "de")
								<img src="/img/Firefox.png" width="100%" />
							@elseif(LaravelLocalization::getCurrentLocale() == "es")
								<img src="/img/FirefoxEs.png" width="100%" />
							@else
								<img src="/img/FirefoxEn.png" width="100%" />
							@endif
						</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v61.2') }}
							@if(LaravelLocalization::getCurrentLocale() == "de")
								<img src="/img/Firefox_Standard.png" width="100%" />
							@elseif(LaravelLocalization::getCurrentLocale() == "es")
								<img src="/img/FirefoxEs_Standard.png" width="100%" />
							@else
								<img src="/img/FirefoxEn_Standard.png" width="100%" />
							@endif
						</li>
					</ol>
				</div>
				<div class="card-heavy">
					<h3>{{ trans('plugin-page.default-page') }}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-firefox.default-page-v61.1') !!}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.2') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.3') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v61.4') }}</li>
					</ol>
				</div>
			@elseif (version_compare($agent->version($browser), '57.', '>='))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.1') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.2') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.3') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.4') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v57.5') }}</li>
					</ol>
				</div>
			@else
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.1') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.2') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.3') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-search-v52.4') }}</li>
					</ol>
				</div>
			@endif
			@if (version_compare($agent->version($browser), '61.', '<'))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v52.1') }}</li>
						<li>{{ trans('plugin-desktop/desktop-firefox.default-page-v52.2') }}</li>
					</ol>
				</div>
			@endif

		@elseif ($browser === 'Chrome')
			@if (version_compare($agent->version($browser), '59.', '>='))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-chrome.default-search-v59.1') !!}</li>
						<li>{{ trans('plugin-desktop/desktop-chrome.default-search-v59.2') }}</li>
						<li>{!! trans('plugin-desktop/desktop-chrome.default-search-v59.3') !!}</li>
					</ol>
				</div>
			@elseif (version_compare($agent->version($browser), '53.', '>='))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-chrome.default-search-v53.1') !!}</li>
						<li>{{ trans('plugin-desktop/desktop-chrome.default-search-v53.2') }}</li>
						<li>{!! trans('plugin-desktop/desktop-chrome.default-search-v53.3') !!}</li>
					</ol>
				</div>
			@else
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-chrome.default-search-v49.1') !!}</li>
						<li>{{ trans('plugin-desktop/desktop-chrome.default-search-v49.2') }}</li>
						<li>{!! trans('plugin-desktop/desktop-chrome.default-search-v49.3') !!}</li>
					</ol>
				</div>
			@endif
			<div class="card-heavy">
				<h3>{{ trans('plugin-page.default-page') }}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-chrome.default-page-v49.1') !!}</li>
					<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.2') }}</li>
					<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.3') }}</li>
					<li>{{ trans('plugin-desktop/desktop-chrome.default-page-v49.4') }}</li>
				</ol>
			</div>
			<h1 class="page-title">
			{{ trans('plugin-page.head.7') }}
			</h1>
			<div class="card-heavy">
				<h3>{{ trans('plugin-page.default-search') }}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.1') !!}</li>
					<li>{{ trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.2') }}</li>
					<li>{{ trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.3') }}</li>
					<li>{{ trans('plugin-desktop/desktop-vivaldi.default-search-v3-3.4') }}</li>
				</ol>
			</div>
			<div class="card-heavy">
				<h4>{{ trans('plugin-page.default-page') }}</h4>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-vivaldi.default-page-v3-3.1') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-vivaldi.default-page-v3-3.2') !!}</li>
				</ol>
			</div>

		@elseif ($browser === 'Opera')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-opera.default-search-v36.1') !!}</li>
					<li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.2') }}</li>
					<li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.3') }}</li>
					<li>{{ trans('plugin-desktop/desktop-opera.default-search-v36.4') }}</li>
					<li><small>{!! trans('plugin-page.desktop-unable') !!}</small></li>
				</ol>
			</div>
			<div class="card-heavy">
				<h3>{{ trans('plugin-page.default-page') }}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-opera.default-page-v36.1') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.default-page-v36.2') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.default-page-v36.3') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.default-page-v36.4') !!}</li>
				</ol>
			</div>

		@elseif ($browser === 'IE')
			@if (version_compare($agent->version($browser), '11.', '>='))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-ie.default-search-v11.1') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-ie.default-search-v11.2') !!}</li>
						<li>{{ trans('plugin-desktop/desktop-ie.default-search-v11.3') }}</li>
						<li>{{ trans('plugin-desktop/desktop-ie.default-search-v11.4') }}</li>
						<li>{{ trans('plugin-desktop/desktop-ie.default-search-v11.5') }}</li>
					</ol>
				</div>
			@else 
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-ie.default-search-v9.1') !!}</li>
						<li>{{ trans('plugin-desktop/desktop-ie.default-search-v9.2') }}</li>
					</ol>
				</div>
			@endif
			<div class="card-heavy">
				<h3>{{ trans('plugin-page.default-page') }}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-ie.default-page.1') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-ie.default-page.2') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-ie.default-page.3') !!}</li>
				</ol>
			</div>

		@elseif ($browser === 'Edge')
			@if (version_compare($agent->version($browser), '85.', '>='))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v85.1') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v85.2') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v85.3') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v85.4') !!}</li>
					</ol>
				</div>
			@elseif (version_compare($agent->version($browser), '80.', '>='))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v80.1') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v80.2') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v80.3') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v80.4') !!}</li>
					</ol>
				</div>
			@elseif (version_compare($agent->version($browser), '18.', '>='))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v18.1') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v18.2') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v18.3') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v18.4') !!}</li>
					</ol>
				</div>
			@else
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v15.1') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v15.2') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v15.3') !!}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-search-v15.4') !!}</li>
					</ol>
				</div>
			@endif
			@if (version_compare($agent->version($browser), '85.', '>='))
				<div class="card-heavy">
					<h3>{{ trans('plugin-page.default-page') }}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-edge.default-page-v80.1') !!}</li>
						<li>{{ trans('plugin-desktop/desktop-edge.default-page-v80.2') }}</li>
						<li>{{ trans('plugin-desktop/desktop-edge.default-page-v80.3') }}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-page-v80.4') !!}</li>
					</ol>
				</div>
			@else
				<div class="card-heavy">
					<h3>{{ trans('plugin-page.default-page') }}</h3>
					<ol>
						<li>{!! trans('plugin-desktop/desktop-edge.default-page-v18.1') !!}</li>
						<li>{{ trans('plugin-desktop/desktop-edge.default-page-v18.2') }}</li>
						<li>{!! trans('plugin-desktop/desktop-edge.default-page-v18.3') !!}</li>
					</ol>
				</div>
			@endif

		@elseif ($browser === 'Safari')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol style="list-style:none;">
					<li><small>{!! trans('plugin-page.desktop-unable') !!}</small></li>
				</ol>
				</div>
			<div class="card-heavy">
				<h3>{{ trans('plugin-page.default-page') }}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-safari.default-page.1') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-safari.default-page.2') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-safari.default-page.3') !!}</li>
				</ol>
			</div>
		@endif

	@elseif ($agent->isPhone())
		@if ($browser === 'Firefox')
			@if (version_compare($agent->version($agent->browser()), "80.0") < 0))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{{ trans('plugin-mobile/mobile-firefox.default-search-vlt80.1') }}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.default-search-vlt80.2') }}</li>
					</ol>
				</div>
			@else
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-mobile/mobile-firefox.default-search-v80.1') !!}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.default-search-v80.2') }}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.default-search-v80.3') }}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.default-search-v80.4') }}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.default-search-v80.5') }}</li>
						<code id=search>"https://metager.de/meta/meta.ger3?eingabe=%s"</code>
					</ol>
				</div>
			@endif

		@elseif ($browser === 'Chrome')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-mobile/mobile-chrome.default-search-v83.1') !!}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.default-search-v83.2') }}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.default-search-v83.3') }}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.default-search-v83.4') }}</li>
				</ol>
			</div>
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-page') !!}</h3>
				<ol>
					<li>{!! trans('plugin-mobile/mobile-chrome.default-page-v83.1') !!}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.default-page-v83.2') }}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.default-page-v83.3') }}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.default-page-v83.4') }}</li>
				</ol>
			</div>

		@elseif ($browser === 'Opera')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-mobile/mobile-opera.default-page-v60.1') !!}</li>
					<li>{{ trans('plugin-mobile/mobile-opera.default-page-v60.2') }}</li>
					<li>{{ trans('plugin-mobile/mobile-opera.default-page-v60.3') }}</li>
					<li>{{ trans('plugin-mobile/mobile-opera.default-page-v60.4') }}</li>
					<li>{{ trans('plugin-mobile/mobile-opera.default-page-v60.5') }}</li>
					<li>{!! trans('plugin-page/mobile-unable') !!}</li>
				</ol>
			</div>

		@elseif ($browser === 'Edge')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-mobile/mobile-edge.default-search-v45.1') !!}</li>
					<li>{{ trans('plugin-mobile/mobile-edge.default-search-v45.2') }}</li>
					<li>{{ trans('plugin-mobile/mobile-edge.default-search-v45.3') }}</li>
					<li>{{ trans('plugin-mobile/mobile-edge.default-search-v45.4') }}</li>
				</ol>
			</div>
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-page') !!}</h3>
				<ol>
					<li>{!! trans('plugin-mobile/mobile-edge.default-page-v45.1') !!}</li>
					<li>{{ trans('plugin-mobile/mobile-edge.default-page-v45.2') }}</li>
					<li>{{ trans('plugin-mobile/mobile-edge.default-page-v45.3') }}</li>
					<li>{{ trans('plugin-mobile/mobile-edge.default-page-v45.4') }}</li>
				</ol>
			</div>
		@endif
	@endif

	@endsection
