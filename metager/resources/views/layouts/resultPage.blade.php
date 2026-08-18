<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}"
    @if(app(App\SearchSettings::class)->theme !== "system") data-theme="{{ app(App\SearchSettings::class)->theme }}" @endif>

<head>
    <meta charset="utf-8">
    @foreach (LaravelLocalization::getSupportedLocales() as $locale => $locale_data)
        @if (LaravelLocalization::getCurrentLocale() !== $locale)
            <link rel="alternate" hreflang="{{ $locale }}"
                href="{{ LaravelLocalization::getLocalizedUrl($locale, null, [], true) }}">
        @endif
    @endforeach
    <link href="/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon" />
    @foreach (scandir(public_path('img/favicon')) as $file)
        @if (in_array($file, ['.', '..']))
            @continue
        @endif
        @php
            preg_match("/(\d+)\.png$/", $file, $matches);
        @endphp
        @if ($matches)
            <link rel="icon" sizes="{{ $matches[1] }}x{{ $matches[1] }}" href="/img/favicon/{{ $file }}" type="image/png">
            <link rel="apple-touch-icon" sizes="{{ $matches[1] }}x{{ $matches[1] }}" href="/img/favicon/{{ $file }}"
                type="image/png">
        @endif
    @endforeach
    <link rel="search" type="application/opensearchdescription+xml"
        title="{{ \App\Http\Controllers\StartpageController::GET_PLUGIN_SHORT_NAME() }}"
        href="{{  action([App\Http\Controllers\StartpageController::class, 'loadPlugin']) }}">
    <link href="/fonts/liberationsans/stylesheet.css" rel="stylesheet">


    {{-- One stylesheet, both palettes: it picks between them from the data-theme
         attribute on <html> and prefers-color-scheme. This used to be up to four
         links, one of which loaded metager.less a second time. --}}
    <link type="text/css" rel="stylesheet" href="{{ Vite::asset('resources/less/metager/metager.less') }}" />
    {{-- type="module" is not optional: Vite emits ES modules, and a bundle that
         imports a shared chunk is a syntax error when loaded as a classic
         script, so the file downloads and never runs. Modules defer by
         default, which is why the explicit defer below is gone; async is kept
         where it was, since it still means what it meant. --}}
    <script type="module" src="{{ Vite::asset('resources/js/scriptResultPage.js') }}"></script>
    @if (!empty($js))
        @foreach ($js as $js_file)
            <script type="module" src="{{ $js_file }}" async></script>
        @endforeach
    @endif

    <title>{{ $eingabe }} - MetaGer</title>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no" name="viewport" />
    <meta name="p" content="{{ getmypid() }}" />
    <meta name="q" content="{{ $eingabe }}" />
    <meta name="l" content="{{ App\Localization::getLanguage() }}" />
    <meta name="searchkey" content="{{ $metager->getSearchUid() }}" />
    <meta name="referrer" content="origin-when-cross-origin">
    <meta name="age-meta-label" content="age=18" />
    <meta name="statistics-enabled" content="{{ config("metager.matomo.enabled") }}">
    @if(!in_array(app(\App\SearchSettings::class)->suggestion_provider, [null, "off"]))
        <meta name="suggestions-enabled" content="true">
    @endif
    {{-- Add Advertisement Scripts if Yahoo is enabled --}}
    @if (app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo') !== null)
        <meta name="source_tag"
            content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->configuration->getParameter->Partner }}" />
        <meta name="ysid"
            content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->search_id }}" />
        <meta name="cid"
            content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->client_id }}" />
        <meta name="ig"
            content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->impression_guid }}" />
        <meta name="clarityId" content="iiolvwkqcy" />
        <meta name="rguid"
            content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->rguid }}" />
        <meta name="test_mode"
            content="{{ app(\App\Models\Configuration\Searchengines::class)->getEnabledSearchengine('yahoo')->test_mode }}" />
    @endif
    @include('parts.utility')
</head>

<body id="resultpage-body" class="{{ app(\App\SearchSettings::class)->fokus }}">
    <div class="skiplinks">
        <div>@lang('resultPage.skiplinks.heading')</div>
        <a href="#results">@lang('resultPage.skiplinks.results')</a>
        <a href="#eingabe">@lang('resultPage.skiplinks.query')</a>
        <a href="#settings-link">@lang('resultPage.skiplinks.settings')</a>
        <a href="#sidebarToggle">@lang('resultPage.skiplinks.navigation')</a>
        <div class="escape">@lang('resultPage.skiplinks.return')</div>
    </div>
    @if (Request::getHttpHost() === 'metager3.de')
        <div class="alert alert-info metager3-unstable-warning-resultpage">
            {!! @trans('resultPage.metager3') !!}
        </div>
    @endif
    @if (!isset($suspendheader))
        @include('layouts.researchandtabs')
    @else
        <link rel="stylesheet" href="{{ Vite::asset('resources/css/noheader.css') }}">
        <div id="resultpage-container-noheader">
            <div id="results-container">
                <span name="top"></span>
                @include('parts.errors')
                @include('parts.warnings')
                @yield('results')
                <div id="backtotop"><a href="#top">@lang('results.backtotop')</a></div>
            </div>
            @include('parts.enginefooter')
        </div>
        @include('parts.footer', ['type' => 'resultpage', 'id' => 'resultPageFooter'])
    @endif
    @include('parts.sidebar', ['id' => 'resultPageSideBar'])
    @include('parts.sidebar-opener', ['class' => 'fixed'])
</body>

</html>