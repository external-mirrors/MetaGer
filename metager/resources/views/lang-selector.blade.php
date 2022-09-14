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
        <li><a @if(LaravelLocalization::getCurrentLocale() === $locale)class="active" @endif rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedURL($locale, null, [], true) }}">{{ $locale_native }}</a></li>
        @endforeach
    </ul>
    @endforeach
</div>
@endsection