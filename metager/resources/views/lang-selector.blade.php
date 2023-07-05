@extends('layouts.subPages', ['page' => 'key'])

@section('title', $title )

@section('content')
<h1>{{ __("lang-selector.h1.1") }}</h1>
@if($previous_url !== null)<div><a  class=back-button href="{{$previous_url}}"><img class="back-arrow" src=/img/back-arrow.svg>{{__("results.zurueck")}}</a></div>@endif
<div>{{ __("lang-selector.description") }}</div>
<div id="languages">
    @foreach(App\Localization::getLanguageSelectorLocales() as $language => $locales)
    <div class="language">
        <h2>{{ trans("lang-selector.lang.$language", [], $language) }}</h2>
        <ul>
            @foreach($locales as $locale => $locale_native)
            @if(LaravelLocalization::getCurrentLocale() === $locale)
            <li class="active">{{ $locale_native }}</li>
            @else
            <li><a rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedURL($locale, route("lang-selector", ["previous_url" => $previous_url]), true) }}">{{ $locale_native }}</a></li>
            @endif
            @endforeach
        </ul>
    </div>
    @endforeach
</div>
<div>@lang("lang-selector.detection")</div>
<div>@lang("lang-selector.storage", ["pluginlink" => LaravelLocalization::getLocalizedUrl(null, route("plugin"))])</div>
@endsection