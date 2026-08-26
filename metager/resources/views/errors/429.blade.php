@extends('layouts.subPages')

@section('title', trans('429.title'))

{{--
	429.title is translated as one string, "429 - <message>", in every
	locale (see lang/*/429.php) — split it here rather than adding a
	second translation key, so the big status-code badge below matches
	the other error pages without touching 12 language files.
--}}
@section('content')
	<div class="error-page">
		<div class="error-page__code">{{ Str::before(trans('429.title'), ' - ') }}</div>
		<h1 class="error-page__title">{{ Str::after(trans('429.title'), ' - ') }}</h1>
	</div>
@endsection
