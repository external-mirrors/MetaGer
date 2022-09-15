@extends('layouts.subPages', ['page' => 'key'])

@section('title', $title )

@section('content')
<h1>{{ __("lang-selector.h1.1") }}</h1>
<p>{{ __("lang-selector.p.1") }}</p>
<div id="languages">
    @foreach(App\Localization::getLanguageSelectorLocales() as $language => $locales)
    <h2>{{ trans("lang-selector.lang.$language", [], $language) }}</h2>
    <ul>
        @foreach($locales as $locale => $locale_native)
        @if(LaravelLocalization::getCurrentLocale() === $locale)
        <li>{{ $locale_native }}</li>
        @else
        <li><a rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedURL($locale, null, [], true) }}">{{ $locale_native }}</a></li>
        @endif
        @endforeach
    </ul>
    @endforeach
</div>
@endsection