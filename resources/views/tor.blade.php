@extends('layouts.subPages')

@section('title', $title )

@section('content')
	<h1>MetaGer hidden service</h1>
	<p>@lang('tor.description')</p>
	<a class="btn btn-primary" href="http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/" role="button">{{trans('tor.torbutton')}}</a>
@endsection
