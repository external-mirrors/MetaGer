<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}">

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


    <link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
    @if (app(App\SearchSettings::class)->theme === 'dark')
        <link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager-dark.css') }}" />
    @elseif(app(App\SearchSettings::class)->theme === 'light')
        <link type="text/css" rel="stylesheet" href="{{ mix('css/themes/metager.css') }}" />
    @elseif(Request::input('out', '') !== 'results-with-style')
        <link type="text/css" rel="stylesheet" media="(prefers-color-scheme:dark)"
            href="{{ mix('css/themes/metager-dark.css') }}" />
    @endif
    @foreach($css as $cssFile)
        <link href="{{ $cssFile }}" rel="stylesheet" />
    @endforeach
    @if (!empty($js))
        @foreach ($js as $js_file)
            <script src="{{ $js_file }}" defer async></script>
        @endforeach
    @endif

    <title>@lang('titles.assistant')</title>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no" name="viewport" />
    <meta name="l" content="{{ App\Localization::getLanguage() }}" />
    <meta name="referrer" content="origin-when-cross-origin">
    <meta name="age-meta-label" content="age=18" />
    <meta name="statistics-enabled" content="{{ config("metager.matomo.enabled") }}">
    <meta name="color-scheme" content="dark light">
    @include('parts.utility')
</head>

<body id="resultpage-body" class="{{ app(\App\SearchSettings::class)->fokus }}">
    @section('results')
        <div id="chat">
            @if(sizeof($assistant->getMessages()) === 0)
                <div id="empty-chat" class="disabled">
                    <div>@lang("assistant.chat.empty_chat.description")</div>
                    <b>@lang("assistant.chat.empty_chat.help")</b>
                </div>
            @else
                @foreach($assistant->getMessages() as $message)
                    @if($message->type === App\Models\Assistant\MessageType::User)
                        <div class="message user">
                            {{ $message->render() }}
                        </div>
                    @elseif($message->type === App\Models\Assistant\MessageType::Agent)
                        <div class="message agent">
                            {!! $message->render() !!}
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
        @if($assistant->can(App\Models\Assistant\AssistantCapability::CHAT))
            <div class="chat-form">
                <form action="{{ route("assistant") }}" method="POST">
                    <input type="hidden" name="history" value="{{ $history }}">
                    <label class="input-sizer stacked">
                        <textarea rows="1" name="prompt" id="prompt" placeholder="@lang('assistant.prompt.placeholder')"
                            autofocus required></textarea>
                    </label>
                    <button type="submit"><img src="/img/icon-lupe.svg" alt="" aria-hidden="true" id="searchbar-img-lupe">
                    </button>
                    @if($assistant->can(App\Models\Assistant\AssistantCapability::SEARCH))
                        <div class="input-group include-search">
                            <input type="checkbox" name="include-search" id="include-search" checked>
                            <label for="include-search">@lang("assistant.prompt.include_search.label")</label>
                        </div>
                    @endif
                </form>
            </div>
        @endif
    @endsection

    @include('layouts.researchandtabs')
</body>