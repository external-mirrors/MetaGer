<div id="resultpage-container">
    <div id="research-bar-container">
        <div id="research-bar">
            <div id="header-logo">
                <a class="screen-large"
                    href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}">
                    <h1><img src="/img/metager.svg" alt="MetaGer" /></h1>
                </a>
                <a class="screen-small"
                    href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), '/') }}">
                    <h1><img src="/img/svg-icons/metager-lock-orange.svg" alt="MetaGer" /></h1>
                </a>
                <a class="lang" href="{{ route('lang-selector') }}">
                    <span>{{ App\Localization::getRegion() }}</span>
                </a>
            </div>
            <div id="header-searchbar">
                @include('parts.searchbar', [
                    'class' => 'resultpage-searchbar',
                    'request' => Request::method(),
                ])
            </div>
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
    <div id="results-container" @if (!empty($metager) && sizeof($metager->getResults()) === 0) class="no-results" @endif>
        <span name="top"></span>
        @include('parts.errors')
        @include('parts.warnings')
        @yield('results')
        @if(app()->make(App\SearchSettings::class)->fokus !== "assistant")
            @include('parts.enginefooter')
        @endif
    <div id="backtotop"><a href="#top">@lang('results.backtotop')</a></div>
    </div>
    @if(!empty($quicktips))
        @include('parts.quicktips', ['quicktips' => $quicktips])
    @endif
    @include('parts.footer', ['type' => 'resultpage', 'id' => 'resultPageFooter'])
</div>
