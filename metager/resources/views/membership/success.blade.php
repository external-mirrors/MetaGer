@extends('layouts.subPages')

@section('title', $title )

@section('navbarFocus.donate', 'class="dropdown active"')

@section('content')
<h1 class="page-title">@lang('membership.title')</h1>
<div class="success">@lang('membership.success')</div>
<a class="btn btn-default" href="{{ LaravelLocalization::getLocalizedURL(null, '/') }}">@lang('membership.startpage')</a>
@endsection