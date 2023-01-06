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
    </style>
</head>
<body>
    <iframe id="mg-framed" src="{{ $frame_url }}" autofocus="true"></iframe>
    <script nonce="{{ $mgv }}">
        document.getElementById("mg-framed").src = "";
    </script>
</body>
