@extends('layouts.subPages')

@section('title', $title )

@section('content')
	<h1>MetaGer hidden service</h1>
	<p>@lang('tor.description')</p>
	<a class="btn btn-primary" href="{{trans('tor.torurl')}}" role="button">{{trans('tor.torbutton')}}</a>
@endsection
