@extends('layouts.subPages')

@section('title', $title)

@section('content')
<div id="overview">
    <h1>MetaGer Logs API</h1>
    <p>@lang('logs.overview.hint')</p>
    <div id="settings">
        @include("logs.parts.invoice-data")
        @if(!$edit_invoice)
            @include("logs.parts.abo")
        @endif
    </div>
    @if(!$edit_invoice)
        @include("logs.parts.orders")
        @include("logs.parts.api_keys")
    @endif
</div>
@endsection