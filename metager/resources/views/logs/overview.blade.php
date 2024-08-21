@extends('layouts.subPages')

@section('title', $title)

@section('content')
<div id="overview">
    <h1>MetaGer Logs API</h1>
    <p>@lang('logs.overview.hint')</p>
    @include("logs..parts.invoice-data")
    @if(!$edit_invoice)

    @endif
</div>
@endsection