@php
    // The chat focus queries no search engines and produces no result list, so most of the result
    // page chrome describes something that isn't happening: a search bar that would abandon the
    // conversation, a foki switcher for a search that was never run, a filter/settings row scoped
    // to engines, an "engines queried" footer listing none, and a back-to-top link on a page that
    // does not scroll. All of it is skipped rather than hidden with CSS.
    //
    // The way back to search is the header logo, which links to the start page on every MetaGer
    // page — plus the sidebar's "Suche" entry (parts/sidebar.blade.php #navigationSuche). Both stay.
    $chatFocus = app(\App\SearchSettings::class)->fokus === 'chat';
@endphp
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
                    <span>{{ App\Localization::getRegion() }}</span>
                </a>
            </div>
            @unless ($chatFocus)
                <div id="header-searchbar">
                    @include('parts.searchbar', [
                        'class' => 'resultpage-searchbar',
                        'request' => Request::method(),
                    ])
                </div>
            @endunless
            @include('parts.sidebar-opener', ['class' => 'fixed'])
        </div>
    </div>
    @unless ($chatFocus)
        <div id="foki">
            <div class="scrollbox">
                <div id="foki-box">
                    @include('parts.foki')
                </div>
            </div>
        </div>
        @include('parts.filter')
    @endunless
    <div id="results-container" @if (!$chatFocus && sizeof($metager->getResults()) === 0) class="no-results" @endif>
        <span name="top"></span>
        @include('parts.errors')
        @include('parts.warnings')
        @yield('results')
        @unless ($chatFocus)
            @include('parts.enginefooter')
            <div id="backtotop"><a href="#top">@lang('results.backtotop')</a></div>
        @endunless
    </div>
    @unless ($chatFocus)
        @include('parts.quicktips', ['quicktips' => $quicktips])
    @endunless

    @include('parts.footer', ['type' => 'resultpage', 'id' => 'resultPageFooter'])
</div>
