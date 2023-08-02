@extends('layouts.subPages')

@section('title', $title )

@section('content')
<div class="static-page-header">
	<h1>{{ trans('adblocker.heading') }}</h1>
</div>
<div class="content">
    <div>@lang('adblocker.free', ['keylink' => app(\App\Models\Authorization\Authorization::class)->getAdFreeLink()])</div>
    <div>{{ trans('adblocker.adblocker') }}</div>
    <ol class="options">
        <li>{{ trans('adblocker.options.disable') }}</li>
        <li>@lang('adblocker.options.key', ['keylink' => app(\App\Models\Authorization\Authorization::class)->getAdFreeLink()])</li>
    </ol>
</div>
@endsection