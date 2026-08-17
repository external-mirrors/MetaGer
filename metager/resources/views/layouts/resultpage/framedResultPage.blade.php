<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}"
    @if(app(App\SearchSettings::class)->theme !== "system") data-theme="{{ app(App\SearchSettings::class)->theme }}" @endif>
<head>
    <meta charset="UTF-8">
    <title>{{ Request::input('eingabe', '') }} - MetaGer</title>
    <meta name="nonce" content="{{ $mgv }}">
    <meta name="url" content="{!! $js_url !!}">
    @foreach(LaravelLocalization::getSupportedLocales() as $locale => $locale_data)
	@if(LaravelLocalization::getCurrentLocale() !== $locale)
	<link rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedUrl($locale, null, [], true) }}">
	@endif
	@endforeach
    {{-- One stylesheet, both palettes; see the data-theme attribute above. --}}
    <link type="text/css" rel="stylesheet" href="{{ Vite::asset('resources/less/metager/metager.less') }}" />
    <script src="{{ Vite::asset('resources/js/verify.js') }}"></script>
    <link rel="stylesheet" href="{{ LaravelLocalization::getLocalizedURL(null, '/index.css?id=' . $mgv) }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <script src="{{Vite::asset('resources/js/utility.js')}}"></script>
    <meta http-equiv="refresh" content="1">
</head>
<body>
	<div id="resultpage-container">
        <div id="research-bar-container">
            <div id="research-bar">
                <div id="header-logo">
                    <a class="screen-large" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif tabindex="4">
                        <h1><img src="/img/metager.svg" alt="MetaGer" /></h1>
                    </a>
                    <a class="screen-small" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif>
                        <h1><img src="/img/svg-icons/metager-lock-orange.svg" alt="MetaGer" /></h1>
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
        <div id="foki"></div>
        <div id="options"></div>
        <div id="results-container"></div>
        <div id="additions-container"></div>
        <footer class="resultPageFooter noprint"></footer>
    </div>
</body>
