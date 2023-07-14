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
            <li><a rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedURL($locale, '/lang?' . http_build_query(['previous_url' => $previous_url, 'switch' => 1]), true) }}">{{ $locale_native }}</a></li>
            @endif
            @endforeach
        </ul>
    </div>
    @endforeach
</div>
<div id="faq">
    <details>
        <summary>@lang("lang-selector.detection.title")</summary>
        <p>@lang("lang-selector.detection.description")</p>
    </details>
    <details>
        <summary>@lang("lang-selector.translate.title")</summary>
        <p>@lang("lang-selector.translate.description", ["contactlink" => route("contact")])</p>
    </details>  
</div>
@endsection