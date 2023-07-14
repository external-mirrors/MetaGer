<div id="resultpage-container">
	<div id="research-bar-container">
		<div id="research-bar">
			<div id="header-logo">
				<a class="screen-large" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif tabindex="4">
					<h1><img src="/img/metager.svg" alt="MetaGer" /></h1>
				</a>
				<a class="screen-small" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif>
					<h1><img src="/img/metager-schloss-orange.svg" alt="MetaGer" /></h1>
				</a>
				<a class="lang" href="{{ route("lang-selector") }}">
					<span>{{ App\Localization::getRegion() }}</span>
				</a>
			</div>
			<div id="header-searchbar">
				@include('parts.searchbar', ['class' => 'resultpage-searchbar', 'request' => Request::method()])
			</div>
			<div class="sidebar-opener-placeholder"></div>
		</div>
	</div>
	<div id="foki">
		<div class="scrollbox">
			<div id="foki-box">
				@include('parts.foki')
			</div>
		</div>
	</div>
	@include('parts.filter')
	<div id="results-container">
		<span name="top"></span>
		@include('parts.errors')
		@include('parts.warnings')
		@yield('results')
		@include('parts.enginefooter')
		<div id="backtotop"><a href="#top">@lang('results.backtotop')</a></div>
	</div>
	@include('parts.quicktips', ["quicktips" => $quicktips])
	
	@include('parts.footer', ['type' => 'resultpage', 'id' => 'resultPageFooter'])
</div>