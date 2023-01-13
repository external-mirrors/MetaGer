@extends('layouts.subPages')

@section('title', $title )

@section('content')
<h1 class="page-title">MetaGer hidden service</h1>
<div class="card">
	<p>@lang('tor.description')</p>
	<a class="btn btn-primary" href="http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/{{LaravelLocalization::getCurrentLocale()}}" role="button">{{trans('tor.torbutton')}}</a>
</div>
@endsection