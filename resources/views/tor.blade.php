@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">MetaGer hidden service</h1>
<div class="card">
	<p>@lang('tor.description')</p>
	<a class="btn btn-primary" href="{{trans('tor.torurl')}}" role="button">{{trans('tor.torbutton')}}</a>
</div>
@endsection