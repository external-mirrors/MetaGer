@extends('layouts.subPages')

@section('title', trans('429.title'))

@section('content')
	<style>
		main#main-content {
			align-items: center;
			justify-content: center;
		}
	</style>
	<h1>{{ trans('429.title') }}</h1>
	<p>{{ trans('429.text') }}</p>
@endsection
