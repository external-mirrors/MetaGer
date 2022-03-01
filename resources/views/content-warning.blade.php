@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div class="card">
    <h1>@lang('content-warning.header1')</h1>
    <p>@lang('content-warning.p1', ["url" => route('faktencheck')])</p>
    <div class="actions">
        <a class="btn btn-default" href="{{ Request::input('url') }}">@lang('content-warning.to_website')</a>
        <a href="{{ Request::input('result-page') }}" onclick="history.back(); return history.length == 1 ? true : false;" rel="prev" class="btn btn-inverted">@lang('content-warning.back_to_search')</a>
    </div>
</div>
@endsection