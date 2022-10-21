<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/index.css?id={{ $key }}">
    <script src="{{ mix('js/index.js') }}"></script>
    @foreach(LaravelLocalization::getSupportedLocales() as $locale => $locale_data)
	@if(LaravelLocalization::getCurrentLocale() !== $locale)
	<link rel="alternate" hreflang="{{ $locale }}" href="{{ LaravelLocalization::getLocalizedUrl($locale, null, [], true) }}">
	@endif
	@endforeach