<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="nonce" content="{{ $mgv }}">
    <meta name="url" content="{!! $js_url !!}">
    <link rel="stylesheet" href="/index.css?id={{ $mgv }}">
    <script src="{{ mix('js/index.js') }}"></script>
    @foreach(LaravelLocalization::getSupportedLocales() as $locale => $locale_data)
	@if(LaravelLocalization::getCurrentLocale() !== $locale)
	<link rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedUrl($locale, null, [], true) }}">
	@endif
	@endforeach
    <link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
	@if(Cookie::get('dark_mode') === "2")
	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager-dark.css') }}" />
	@elseif(Cookie::get('dark_mode') === "1")
	<link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
	@elseif(Request::input('out', '') !== "results-with-style" )
	<link type="text/css" rel="stylesheet" media="(prefers-color-scheme:dark)" href="{{ mix('css/themes/metager-dark.css') }}" />
	@endif
    <title>{{ Request::input('eingabe', '') }} - MetaGer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

    <style nonce="{{ $mgv }}">
        html {
            height: 100%;
        }

        body {
            margin: 0;
            height: 100%;
        }

        iframe#mg-framed {
            display: block;
            width: 100%;
            border: 0;
            height: 100%;
            height: 100vh;
        }
        html > body > #resultpage-container {
            display: none;
        }
    </style>
</head>
<body>
	<div id="resultpage-container">
        <div id="whitespace"></div>
        <div id="research-bar-container">
            <div id="research-bar">
                <div id="header-logo">
                    <a class="screen-large" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif tabindex="4">
                        <h1><img src="/img/metager.svg" alt="MetaGer" /></h1>
                    </a>
                    <a class="screen-small" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}" @if(!empty($metager) && $metager->isFramed())target="_top" @endif>
                        <h1><img src="/img/metager-schloss-orange.svg" alt="MetaGer" /></h1>
                    </a>
                    <a class="lang" href="{{ LaravelLocalization::getLocalizedURL(null, route("lang-selector")) }}">
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
    <noscript>
        <iframe id="mg-framed" src="{{ $frame_url }}" autofocus="true"></iframe>
    </noscript>
    <script nonce="{{ $mgv }}">
        let frame = document.getElementById("mg-framed");
        if(frame != null){
            document.getElementById("mg-framed").src = "";
        }
        document.getElementById("resultpage-container").style.display = "grid";
    </script>
</body>
