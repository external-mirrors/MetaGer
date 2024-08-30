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
        <div id="api-docs">
            <div>@lang("logs.api-docs.hint")</div>
            <a href="https://gitlab.metager.de/open-source/MetaGer/-/blob/master/metager/app/Models/Logs/logs.md?ref_type=heads"
                target="_blank">@lang("logs.api-docs.link")</a>
        </div>
    @endif
</div>
@endsection