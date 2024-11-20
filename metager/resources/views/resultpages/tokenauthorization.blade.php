@extends('layouts.subPages')

@section('title', $title)

@section('content')
@if(isset($cost) && isset($resultpage))
    <input type="hidden" id="tokencost" value="{{$cost}}">
    <input type="hidden" id="resultpage" value="{{$resultpage}}">
@elseif(isset($payment) && isset($method) && isset($page) && isset($parameters) && isset($error_url))
    <input type="hidden" name="payment" value="{{$payment}}" id="payment">
    <input type="hidden" name="error_url" id="error_url" value="{{$error_url}}">
    <form action="{{$page}}" method="{{$method}}" id="form">
        @foreach($parameters as $key => $value)
            <input type="hidden" name="{{$key}}" value="{{$value}}">
        @endforeach
    </form>
@endif
@endsection