@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.tips', 'class="active"')

@section('content')<div role="dialog">
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
		@elseif ($browser === 'Vivaldi')
			{{ trans('plugin-page.head.7') }}
		@else
			$(".seperator").addClass("hidden");
		@endif
	</h1>
	@if ($agent->isDesktop())
		@if ($browser === 'Firefox' || $browser === 'Mozilla')
			<div class="card-medium">
				<h3>{!! trans('plugin-desktop/desktop-firefox.firefox.plugin') !!}</h3>
			</div>
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search', ['browser' => $browser]) !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-firefox.firefox.1') !!}
						@if(LaravelLocalization::getCurrentLocale() == "de")
							<img src="/img/Firefox.png" width="100%" />
						@elseif(LaravelLocalization::getCurrentLocale() == "es")
							<img src="/img/FirefoxEs.png" width="100%" />
						@else
							<img src="/img/FirefoxEn.png" width="100%" />
						@endif
					</li>
					<li>{!! trans('plugin-desktop/desktop-firefox.firefox.2') !!}
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
				<h3>{!! trans('plugin-desktop/desktop-firefox.firefox.3', ['browser' => $browser]) !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-firefox.firefox.4') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-firefox.firefox.5') !!}</li>
				</ol>
			</div>
			<div class="card-heavy">
				<h4>{{ trans('plugin-page.head.8') }}</h4>
				<ol>
					<li>{!! trans('plugin-page.firefox-klar.1') !!}</li>
					<li>{{ trans('plugin-page.firefox-klar.2')}}</li>
					<li>{{ trans('plugin-page.firefox-klar.3') }}</li>
					<li>{{ trans('plugin-page.firefox-klar.4') }}</li>
				</ol>
			</div>
		@elseif ($browser === 'Chrome')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-chrome.chrome59.1') !!}</li>
					<li>{{ trans('plugin-desktop/desktop-chrome.chrome59.2') }}</li>
					<li>{!! trans('plugin-desktop/desktop-chrome.chrome59.3') !!}</li>
				</ol>
			</div>
			<div class="card-heavy">
				<h3>{!! trans('plugin-desktop/desktop-chrome.chrome.4', ['browser' => $browser]) !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-chrome.chrome.5') !!}</li>
					<li>{{ trans('plugin-desktop/desktop-chrome.chrome.6') }}</li>
					<li>{{ trans('plugin-desktop/desktop-chrome.chrome.7') }}</li>
					<li>{{ trans('plugin-desktop/desktop-chrome.chrome.8') }}</li>
				</ol>
			</div>
		@elseif ($browser === 'Opera')
			<div class="card-heavy">
				<h3>{!! trans('plugin-desktop/desktop-opera.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-opera.opera36.1') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.opera36.2') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.opera36.3') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.opera36.4') !!}</li>
					<li><small>{!! trans('plugin-desktop/desktop-opera36.opera.5') !!}</small>
				</ol>
			</div>
			<div class="card-heavy">
				<h3>{!! trans('plugin-desktop/desktop-opera.opera36.6', ['browser' => $browser]) !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-opera.opera36.7') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.opera36.8') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.opera36.9') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-opera.opera36.10') !!}</li>
				</ol>
			</div>
		@elseif ($browser === 'IE')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-ie.IE11.1') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-ie.IE11.2') !!} (<i class="fa fa-cog" aria-hidden="true"></i>)</li>
					<li>{!! trans('plugin-desktop/desktop-ie.IE11.3') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-ie.IE11.4') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-ie.IE11.5') !!}</li>
				</ol>
			</div>
			<div class="card-heavy">
				<h3>{!! trans('plugin-desktop/desktop-ie.IE.8', ['browser' => $browser]) !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-ie.IE.9') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-ie.IE.10') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-ie.IE.11') !!}</li>
				</ol>
			</div>
		@elseif ($browser === 'Edge')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-edge.edge85.1') !!}<i class="fa fa-ellipsis-h" aria-hidden="true"></i>{!! trans('plugin-desktop/desktop-edge.edge.2') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-edge.edge85.3') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-edge.edge85.4') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-edge.edge85.5') !!}</li>
				</ol>
			</div>
			<div class="card-heavy">
				<h3>{!! trans('plugin-desktop/desktop-edge.edge.6', ['browser' => $browser]) !!}</h3>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-edge.edge.7') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-edge.edge.8') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-edge.edge.9') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-edge.edge.10') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-edge.edge.11') !!}</li>
				</ol>
			</div>
		@elseif ($browser === 'Safari')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-page.desktop-unable') !!}</li>
				</ol>
			</div>
		@elseif ($browser === 'Vivaldi')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{{ trans('plugin-desktop/desktop-vivaldi.vivaldi3-3.1') }}</li>
					<li>{{ trans('plugin-desktop/desktop-vivaldi.vivaldi3-3.2') }}</li>
					<li>{{ trans('plugin-desktop/desktop-vivaldi.vivaldi3-3.3') }}</li>
					<li>{{ trans('plugin-desktop/desktop-vivaldi.vivaldi3-3.4') }}</li>
				</ol>
			</div>
			<div class="card-heavy">
				<h4>{!! trans('plugin-desktop/desktop-vivaldi.vivaldi3-3.8', ['browser' => $browser]) !!}</h4>
				<ol>
					<li>{!! trans('plugin-desktop/desktop-vivaldi.vivaldi3-3.9') !!}</li>
					<li>{!! trans('plugin-desktop/desktop-vivaldi.vivaldi3-3.10') !!}</li>
				</ol>
			</div>
		@endif
	@elseif ($agent->isPhone())
		@if ($browser === 'Firefox')
			@if (version_compare($agent->version($agent->browser()), "80.0") < 0))
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{{ trans('plugin-mobile/mobile-firefox.mfirefoxlt80.1') }}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.mfirefoxlt80.2') }}</li>
					</ol>
				</div>
			@else
				<div class="card-heavy">
					<h3>{!! trans('plugin-page.default-search') !!}</h3>
					<ol>
						<li>{!! trans('plugin-mobile/mobile-firefox.mfirefox.1') !!}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.mfirefox.2') }}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.mfirefox.3') }}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.mfirefox.4') }}</li>
						<li>{{ trans('plugin-mobile/mobile-firefox.mfirefox.5') }}</li>
						<code id=search>"https://metager.de/meta/meta.ger3?eingabe=%s"</code>
					</ol>
				</div>
			@endif
		@elseif ($browser === 'Chrome')
			<div class="card-heavy">
				<h3>{!! trans('plugin-page.default-search') !!}</h3>
				<ol>
					<li>{!! trans('plugin-mobile/mobile-chrome.mchrome.1') !!}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.mchrome.2') }}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.mchrome.3') }}</li>
					<li>{{ trans('plugin-mobile/mobile-chrome.mchrome.4') }}</li>
				</ol>
			</div>
		@endif
	@endif

	@endsection
