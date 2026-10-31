<div id="resultpage-container">
    <div id="research-bar-container">
        <div id="research-bar">
            <div id="header-logo">
                <a class="screen-large"
                    href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}"
                    @if (!empty($metager) && $metager->isFramed()) target="_top" @endif >
                    <h1><img src="/img/metager.svg" alt="MetaGer" /></h1>
                </a>
                <a class="screen-small"
                    href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}"
                    @if (!empty($metager) && $metager->isFramed()) target="_top" @endif>
                    <h1><img src="/img/svg-icons/metager-lock-orange.svg" alt="MetaGer" /></h1>
                </a>
                <a class="lang" href="{{ route('lang-selector') }}">
                    <span>{{ App\Localization::getRegion() }}</span><span class="visually-hidden">@lang('index.lang')</span>
                </a>
            </div>
            <div id="header-searchbar">
                @include('parts.searchbar', [
                    'class' => 'resultpage-searchbar',
                    'request' => Request::method(),
                ])
            </div>
            @include('parts.account-pill', ['density' => 'mark', 'class' => 'account-pill--in-bar'])
            @include('parts.sidebar-opener', ['class' => 'fixed'])
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
    <div id="results-container" @if (sizeof($metager->getResults()) === 0) class="no-results" @endif>
        <span name="top"></span>
        @include('parts.errors')
        @include('parts.warnings')
        @yield('results')
        @include('parts.enginefooter')
        <div id="backtotop"><a href="#top"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 19V5M6 11l6-6 6 6"/></svg>@lang('results.backtotop')</a></div>
    </div>
    @include('parts.quicktips', ['quicktips' => $quicktips])

    @include('parts.footer', ['type' => 'resultpage', 'id' => 'resultPageFooter'])
</div>
