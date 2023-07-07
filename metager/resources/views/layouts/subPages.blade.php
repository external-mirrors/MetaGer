@extends('layouts.staticPages')

@section('homeIcon')
	<div id="subpage-logo">
		<a class="navbar-brand" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}">
			<h1><img src="/img/metager.svg" alt="MetaGer" /></h1>
		</a>
		<a class="lang" href="{{ route("lang-selector") }}">
          <span>{{ App\Localization::getRegion() }}</span>
        </a>
	</div>
@endsection
