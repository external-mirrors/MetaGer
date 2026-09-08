@extends('layouts.subPages')

@section('title', $title )

@section('content')
{{--
    This page is a deliberately plain update source for Obtainium: exactly one
    APK link for the selected channel, and the current version printed as
    "MetaGer <x.y.z>" (App\Landing\AppRelease::VERSION_REGEX) so Obtainium reads
    a real version rather than hashing 100 MB of APK. Keep those two shapes
    stable — AppObtainiumPageTest pins them against the deep link's settings.
--}}
<h1 class="page-title">{{ trans('app.obtainium.title') }}</h1>
<p class="page-subtitle">{{ trans('app.obtainium.intro') }}</p>

@php
    $label = $channel === 'beta' ? 'MetaGer (Beta)' : 'MetaGer';
    $label .= $version ? ' ' . $version : '';
@endphp

<div class="card">
	<h1>{{ $channel === 'beta' ? 'MetaGer Beta' : 'MetaGer' }}</h1>

	<p><a class="btn btn-default" href="{{ $deepLink }}">{{ trans('app.obtainium.add_button') }}</a></p>

	<p>{{ trans('app.obtainium.direct') }}</p>
	<p><a href="{{ $apkUrl }}">{{ $label }} — app-release_manual.apk</a></p>

	<p>
		@if($channel === 'beta')
			<a href="{{ url('app/obtainium') }}">{{ trans('app.obtainium.to_stable') }}</a>
		@else
			<a href="{{ url('app/obtainium') }}?channel=beta">{{ trans('app.obtainium.to_beta') }}</a>
		@endif
	</p>
	<p>{{ trans('app.obtainium.channel_note') }}</p>
</div>

<div class="card">
	<h1>{{ trans('app.obtainium.manual_head') }}</h1>
	<p>{{ trans('app.obtainium.manual_intro') }}</p>
	<p><code>{{ $sourceUrl }}</code></p>
	<ul>
		<li><code>versionExtractionRegEx</code> = <code>{{ $versionRegex }}</code></li>
		<li><code>matchGroupToUse</code> = <code>1</code></li>
		<li><code>versionExtractWholePage</code> = <code>true</code></li>
		<li><code>appId</code> = <code>de.metager.metagerapp.manual</code></li>
	</ul>
</div>

<p><a href="{{ url('app') }}">{{ trans('app.obtainium.back') }}</a></p>
@endsection
