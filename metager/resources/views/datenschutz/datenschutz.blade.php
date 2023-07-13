@extends('layouts.subPages', ['page' => 'privacy'])

@section('title', trans('titles.datenschutz') )

@section('navbarFocus.datenschutz', 'class="active"')

@section('content')
    @if (\App\Localization::getLanguage() == "de")
        @include('datenschutz.german')
	@else
        @include('datenschutz.english')
	@endif
@endsection
